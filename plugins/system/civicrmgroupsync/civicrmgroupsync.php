<?php
/**
 * @version		:  2011-07-23 20:07:15$
 * @author		 
 * @package		CiviCRM Group Sync
 * @copyright	Copyright (C) 2011- . All rights reserved.
 * @license		
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );


class  plgSystemCivicrmGroupSync extends JPlugin
{
	    
        
        
	function onAfterInitialise()
	{  
		return true;
	}

	function onAfterRoute()
	{  
		return true;
	}
	function onAfterDispatch()
	{  
		return true;
	}	

	function onBeforeRender()
	{  
		return true;
	}

	function onAfterRender()
	{  
		return true;
	}

	function onBeforeCompileHead()
	{  
		return true;
	}

	function onContentSearch()
	{  
		return true;
	}

	function onContentSearchAreas()
	{  
		return true;
	}

	function onGetWebServices()
	{  
		return true;
	}

}
