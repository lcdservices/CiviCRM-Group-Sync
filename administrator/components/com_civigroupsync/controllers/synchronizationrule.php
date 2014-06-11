<?php
/**
 * @version     2.0.0
 * @package     com_civigroupsync
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Brian Shaughnessy <brian@lcdservices.biz> - www.lcdservices.biz
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

/**
 * Synchronizationrule controller class.
 */
class CiviGroupSyncControllerSynchronizationrule extends JControllerForm
{

    function __construct() {
        $this->view_list = 'synchronizationrules';
        parent::__construct();
    }

}
