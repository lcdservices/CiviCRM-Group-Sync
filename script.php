<?php
/**
 * @version     2019-04-06
 * @author      Brian Shaughnessy
 * @package     CiviCRM Group Sync
 * @copyright   Copyright (C) 2019. All rights reserved.
 * @license     GNU GPL
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

/*
 * Installer script for package
 */
class com_civiGroupSyncInstallerScript {

  /**
   * method to run during installation
   * installs and enables the plugin
   *
   * @return void
   */
  function install($parent)
  {
    $manifest = $parent->get("manifest");
    $parent = $parent->getParent();
    $source = $parent->getPath("source");

    $installer = new JInstaller();

    // Install plugins
    foreach($manifest->plugins->plugin as $plugin) {
      $attributes = $plugin->attributes();
      $plg = $source.'/'.$attributes['folder'].'/'.$attributes['plugin'];
      $installer->install($plg);
    }

    $db = JFactory::getDbo();
    $tableExtensions = $db->quoteName("#__extensions");
    $columnElement = $db->quoteName("element");
    $columnType = $db->quoteName("type");
    $columnEnabled = $db->quoteName("enabled");

    // Enable plugins
    $db->setQuery("
      UPDATE $tableExtensions
      SET $columnEnabled = 1
      WHERE $columnElement = 'civigroupsync'
      AND $columnType = 'plugin'
    ");
    $db->query();
  }

  function update($parent) {
    $this->install($parent);
  }
}
