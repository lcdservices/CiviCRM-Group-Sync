<?php
/**
 * @version     2011-07-23 20:07:15$
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
    
    function install($parent)
    {
        $manifest = $parent->get("manifest");
        $parent = $parent->getParent();
        $source = $parent->getPath("source");

        $installer = new JInstaller();

        // Install plugins
        foreach($manifest->plugins->plugin as $plugin) {
            $attributes = $plugin->attributes();
            $plg = $source . DS . $attributes['folder'].DS.$attributes['plugin'];
            $installer->install($plg);
        }

        $db = JFactory::getDbo();
        $tableExtensions = $db->nameQuote("#__extensions");
        $columnElement   = $db->nameQuote("element");
        $columnType      = $db->nameQuote("type");
        $columnEnabled   = $db->nameQuote("enabled");

        // Enable plugins
        $db->setQuery( "UPDATE $tableExtensions
                        SET $columnEnabled = 1
                        WHERE $columnElement = 'civigroupsync'
                        AND $columnType = 'plugin'"
                      );
        $db->query();
        
    }
    
    /**
     * method to run after an install/update/uninstall method
     *
     * @return void
     */
    function postflight($type, $parent) 
    {
        // enable system plugin after package install
        /*if ( in_array($type, array('install', 'update', 'discover_install')) ) {
            $db = JFactory::getDbo();
            $db->setQuery("UPDATE #__extensions SET enabled = 1 WHERE element = 'civigroupsync' AND type = 'plugin'");
            $plgEnable = $db->loadResult();
        }*/
    }
}
?>