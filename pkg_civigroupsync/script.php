<?php
/**
 * @version     :  2011-07-23 20:07:15$
 * @author       
 * @package     CiviCRM Group Sync
 * @copyright   Copyright (C) 2011- . All rights reserved.
 * @license     
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

/*
 * Installer script for package
 */
class com_civiGroupSyncInstallerScript {
    
    /**
     * method to run after an install/update/uninstall method
     *
     * @return void
     */
    function postflight($type, $parent) 
    {
        // enable system plugin after package install
        if ( in_array($type, array('install', 'update', 'discover_install')) ) {
            $db = JFactory::getDbo();
            $db->setQuery("UPDATE #__extensions SET enabled = 1 WHERE element = 'civigroupsync' AND type = 'plugin'");
            $plgEnable = $db->loadResult();
        }
    }
}
?>