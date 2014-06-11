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

class CiviGroupSyncController extends JControllerLegacy
{
	/**
	 * Method to display a view.
	 *
	 * @param	boolean			$cachable	If true, the view output will be cached
	 * @param	array			$urlparams	An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return	JController		This object to support chaining.
	 * @since	1.5
	 */
    public function display($cachable = false, $urlparams = false) {
		require_once JPATH_COMPONENT.'/helpers/civigroupsync.php';

        $view = JFactory::getApplication()->input->getCmd('view', 'synchronizationrules');
        JFactory::getApplication()->input->set('view', $view);

        parent::display($cachable, $urlparams);

		return $this;
	}

}
