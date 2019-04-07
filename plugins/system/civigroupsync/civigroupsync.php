<?php
/**
 * @version    2019-04-06
 * @author     Brian Shaughnessy
 * @package    CiviCRM Group Sync
 * @copyright  Copyright (C) 2019. All rights reserved.
 * @license    GNU GPL
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );

class  plgSystemCiviGroupSyncLCD extends JPlugin
{
  
  /*
   * Joomla -> CiviCRM
   * Update CiviCRM groups on Joomla user save
   * Method is called after user data is stored in the database
   *
   * @param   array       $user       Holds the new user data.
   * @param   boolean     $isnew      True if a new user is stored.
   * @param   boolean     $success    True if user was succesfully stored in the database.
   * @param   string      $msg        Message.
   *
   * @return  void
   * @since   1.6
   * @throws  Exception on error.
   */
  function onUserAfterSave( $user, $isnew, $success, $msg ) {

    jimport('joomla.user.helper');
    JFactory::getApplication();

    // Instantiate CiviCRM
    require_once JPATH_ROOT.'/administrator/components/com_civicrm/civicrm.settings.php';
    require_once 'CRM/Core/Config.php';
    require_once 'api/api.php';
    CRM_Core_Config::singleton( );

    // Get sync mappings
    $mappings = self::getCiviGroupSyncMappings();
    if (empty($mappings)) {
      return;
    }

    // Retrieve Joomla User ID and CiviCRM Contact ID
    $juserid = $user['id'];
    $cuser = [];
    try {
      $cuser = civicrm_api3('UFMatch', 'get', [
        'uf_id' => $juserid,
        'sequential' => 1,
      ]);
    }
    catch (CiviCRM_API3_Exception $e) {}

    //CRM_Core_Error::debug_var('$user', $user, true, true, 'cgs');
    //CRM_Core_Error::debug_var('$cuser', $cuser, true, true, 'cgs');

    if (CRM_Utils_Array::value('count', $cuser) > 0) {
      $cuserid = $cuser['values'][0]['contact_id'];
    }
    else {
      return;
    }

    //get the users ACL groups; the $user object is unreliable so retrieve using helper
    $jUserGroups = JUserHelper::getUserGroups($juserid);
        
    //CRM_Core_Error::debug_var('jUserGroups', $jUserGroups, true, true, 'cgs');
    //CRM_Core_Error::debug_var('mappings', $mappings, true, true, 'cgs');

    //cycle through mappings and add to/remove from CiviCRM groups
    foreach ($mappings as $mapping) {
      try {
        if (in_array($mapping['jgroup_id'], $jUserGroups)) {
          $gc1 = civicrm_api3("GroupContact", "create", [
            'group_id' => $mapping['cgroup_id'],
            'contact_id' => $cuserid
          ]);
          //CRM_Core_Error::debug_var('gc1', $gc1, true, true, 'cgs');
        }
        else {
          $gc2 = civicrm_api3("GroupContact", "delete", [
            'group_id' => $mapping['cgroup_id'],
            'contact_id' => $cuserid
          ]);
          //CRM_Core_Error::debug_var('gc2', $gc2, true, true, 'cgs');
        }
      }
      catch (CiviCRM_API3_Exception $e) {}
    }

    return;
  } //end onUserAfterSave
    
  //NOTE: If a user is deleted, we don't alter the contact record
  //NOTE: If a JGroup or CGroup is deleted, we don't remove from the linked group

  /*
   * CiviCRM -> Joomla
   * Update Joomla groups on CiviCRM group-contact add
   * Method is called after group contact subscription is stored in the database
   *
   * @param   string    $op           Operation performed
   * @param   string    $objectName   Name of object
   * @param   int       $objectId     Unique identifier (group)
   * @param   object    &$objectRef   Object reference (contact)
   *
   * @return  void
   * @since   1.6
   */
  public function civicrm_post($op, $objectName, $objectId, &$objectRef) {
    //CRM_Core_Error::debug_var('post $op', $op, true, true, 'cgs');
    //CRM_Core_Error::debug_var('post $objectName', $objectName, true, true, 'cgs');
    //CRM_Core_Error::debug_var('post $objectId', $objectId, true, true, 'cgs');
    //CRM_Core_Error::debug_var('post $objectRef', $objectRef, true, true, 'cgs');

    if (!in_array($objectName, array('GroupContact', 'UFMatch'))) {
      return;
    }

    // Get sync mappings
    $mappings = self::getCiviGroupSyncMappings();
    if (empty($mappings)) {
      return;
    }

    // Get IDs
    switch ($objectName) {
      case 'GroupContact':
        $gids = array($objectId);
        $cid = $objectRef[0];
        try {
          $juser = civicrm_api3("UFMatch", "get", [
            'contact_id' => $cid,
          ]);
        }
        catch (CiviCRM_API3_Exception $e) {}

        //if we can't match with a Joomla user, exit
        if (empty($juser['count'])) {
          return;
        }

        $juserid = $juser['values'][$juser['id']]['uf_id'];

        break;

      case 'UFMatch':
        $cid = $objectRef->contact_id;
        $juserid = $objectRef->uf_id;
        $gids = array();

        try {
          $contactGroups = civicrm_api3("GroupContact", "get", [
            'contact_id' => $cid
          ]);
        }
        catch (CiviCRM_API3_Exception $e) {}

        foreach ($contactGroups['values'] as $cg) {
          $gids[] = $cg['group_id'];
        }
        break;
    }

    // Cycle through mappings and locate jgroup_id
    $jgroup_ids = array();
    foreach ($mappings as $mapping) {
      if (in_array($mapping['cgroup_id'], $gids)) {
        $jgroup_ids[] = $mapping['jgroup_id'];
      }
    }

    // Return if there is no mapped Joomla group
    if (empty($jgroup_ids)) {
      return;
    }

    jimport('joomla.user.helper');

    switch ($op) {
      case 'create':
      case 'edit':
        //add to Joomla group
        foreach ($jgroup_ids as $jgroup_id) {
          JUserHelper::addUserToGroup($juserid, $jgroup_id);
        }
        break;

      case 'delete':
        //remove from Joomla group
        //first check to make sure contact has no other C groups associated with this J group
        foreach ($jgroup_ids as $jgroup_id) {
          if (self::countCiviJoomlaGroups($mappings, $jgroup_id, $gids, $cid) > 1) {
            break;
          }
          else {
            JUserHelper::removeUserFromGroup($juserid, $jgroup_id);
          }
        }
        break;

      default:
        break;
    }
  } //end civicrm_post
    
  /*
   * CiviCRM <-> Joomla
   * Run rules when mapping is created/edited or enabled
   * Note: we don't need to update users/contacts when a JGroup or CGroup
   * is created, as the group must precede the mapping record.
   *
   * Note: we don't modify users/contacts if a sync rule is removed or disabled
   *
   * Method is called right after the content is saved
   *
   * @param   string      The context of the content passed to the plugin (added in 1.6)
   * @param   object      A JTableContent object
   * @param   bool        If the content is just about to be created
   * @since   1.6
   */
  public function onContentAfterSaveLCD($context, $article, $isNew) {
    $ruleID = $article->id;
    $ruleState = $article->state;
    $jgroup_id = $article->jgroup_id;
    $cgroup_id = $article->cgroup_id;

    //if the sync rule is disabled, take no action and exit
    if (!$ruleState) {
      return true;
    }

    //if we are not in the right context, exit
    if (!in_array($context, array(
      'com_civigroupsync.synchronizationrule',
      'com_civigroupsync.synchronizationrules'))
    ) {
      return true;
    }

    //instantiate CiviCRM
    require_once JPATH_ROOT.'/administrator/components/com_civicrm/civicrm.settings.php';
    require_once 'CRM/Core/Config.php';
    require_once 'CRM/Contact/BAO/Group.php';
    require_once 'api/api.php';
    CRM_Core_Config::singleton( );

    //include Joomla files
    jimport('joomla.user.helper');
    jimport('joomla.access.access');
    jimport('joomla.factory');

    //update Joomla groups
    $cGroupContacts = CRM_Contact_BAO_Group::getGroupContacts($cgroup_id);
    foreach ($cGroupContacts as $cGroupContact) {
      $cid = $cGroupContact['contact_id'];

      try {
        $juser = civicrm_api3("UFMatch", "get", [
          'contact_id' => $cid
        ]);
      }
      catch (CiviCRM_API3_Exception $e) {}

      //if we can't match with a Joomla user, move to next record
      if (empty($juser['count'])) {
        continue;
      }

      $juserid = $juser['values'][$juser['id']]['uf_id'];

      //check if user exists
      $user = JFactory::getUser($juserid);
      if ($user->id) {
        //add to Joomla group
        JUserHelper::addUserToGroup($juserid, $jgroup_id);
      }
      else {
        //delete the uf_match record as it is an orphan
        $sql = "DELETE FROM civicrm_uf_match WHERE uf_id = $juserid;";
        $del = CRM_Core_DAO::executeQuery($sql);
      }
    }

    //update CiviCRM groups
    $jGroupContacts = JAccess::getUsersByGroup($jgroup_id);
    foreach ($jGroupContacts as $juserid) {
      try {
        $cuser = civicrm_api3("UFMatch", "get", [
          'uf_id' => $juserid
        ]);
      }
      catch (CiviCRM_API3_Exception $e) {}

      //if we can't match with a CiviCRM user, move to next record
      if (empty($cuser['count'])) {
        continue;
      }

      $cuserid = $cuser['values'][$cuser['id']]['contact_id'];

      //add to CiviCRM group
      try {
        civicrm_api3("GroupContact", "create", [
          'group_id' => $cgroup_id,
          'contact_id' => $cuserid
        ]);
      }
      catch (CiviCRM_API3_Exception $e) {}
    }

    return true;
  } //end onContentAfterSave
    
  /*
   * Helper function to retrieve sync mappings
   *
   * @return  array
   * @since   1.6
   */
  public function getCiviGroupSyncMappings() {

    $db = JFactory::getDbo();
    $db->setQuery("SELECT * FROM #__civigroupsync_rules WHERE state = 1");
    $mappings = $db->loadAssocList($key='id');

    return $mappings;

  } //end getCiviGroupSyncMappings
    
  /*
   * Helper function to check if the user has multiple valid mappings to a Joomla group
   *
   * @return  count of contact-group memberships mapped to passed joomla group
   * @since   1.6
   */
  public function countCiviJoomlaGroups($mappings, $jgroup_id, $gids, $cid) {

    //start count at 1 for group we are removing
    $countCiviGroups = 1;

    //get all cgroup_ids for passed jgroup_id
    $civiGroups = array();
    foreach ($mappings as $mapping) {
      if ($mapping['jgroup_id'] == $jgroup_id) {
        $civiGroups[] = $mapping['cgroup_id'];
      }
    }

    //if civiGroups count is < 2, we can exit immediately
    if (count($civiGroups) < 2) {
      return $countCiviGroups;
    }

    //get contacts group memberships
    try {
      $contactGroups = civicrm_api3("GroupContact", "get", [
        'contact_id' => $cid
      ]);
    }
    catch (CiviCRM_API3_Exception $e) {}

    //now cycle through our list of multiple civiGroups and determine if contact is member of others
    foreach ($civiGroups as $civiGroup) {
      //skip the Civi group ID we are working with
      if (in_array($civiGroup, $gids)) {
        continue;
      }

      foreach ($contactGroups['values'] as $contactGroup) {
        if ($contactGroup['group_id'] == $civiGroup) {
          $countCiviGroups++;
        }
      }
    }

    return $countCiviGroups;
  } //end countCiviJoomlaGroups
}

if (version_compare(JVERSION, '3.0', '<')) {
  class plgSystemCiviGroupSync extends plgSystemCiviGroupSyncLCD {
    public function onContentAfterSave($context, &$article, $isNew) {
      $this->onContentAfterSaveLCD( $context, $article, $isNew );
    }
  }
}
else {
  class plgSystemCiviGroupSync extends plgSystemCiviGroupSyncLCD {
    public function onContentAfterSave($context, $article, $isNew) {
      $this->onContentAfterSaveLCD( $context, $article, $isNew );
    }
  }
}
