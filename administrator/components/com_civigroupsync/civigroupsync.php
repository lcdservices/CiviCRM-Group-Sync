<?php
/**
 * @version     2.0.0
 * @package     com_civigroupsync
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Brian Shaughnessy <brian@lcdservices.biz> - www.lcdservices.biz
 */


// no direct access
defined('_JEXEC') or die;

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_civigroupsync')) 
{
	throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
}

// Include dependancies
jimport('joomla.application.component.controller');

$controller	= JControllerLegacy::getInstance('CiviGroupSync');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
