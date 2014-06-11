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

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('behavior.keepalive');

// Import CSS
$document = JFactory::getDocument();
$document->addStyleSheet('components/com_civigroupsync/assets/css/civigroupsync.css');
?>
<script type="text/javascript">
    js = jQuery.noConflict();
    js(document).ready(function() {
        
    });

	Joomla.submitbutton = function(task)
	{
        if (task == 'synchronizationrule.cancel') {
            Joomla.submitform(task, document.getElementById('synchronizationrule-form'));
        }
        else {
            
            if (task != 'synchronizationrule.cancel' && document.formvalidator.isValid(document.id('synchronizationrule-form'))) {
                
			Joomla.submitform(task, document.getElementById('synchronizationrule-form'));
		}
		else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
    }
</script>

<form action="<?php echo JRoute::_('index.php?option=com_civigroupsync&layout=edit&id=' . (int) $this->item->id); ?>" method="post" enctype="multipart/form-data" name="adminForm" id="synchronizationrule-form" class="form-validate">

    <div class="form-horizontal">
        <?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>
            
        <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'general', JText::_('COM_CIVIGROUPSYNC_TITLE_SYNCHRONIZATIONRULE', true)); ?>
        <div class="row-fluid">
            <div class="span10 form-horizontal">
                <fieldset class="adminform">

                    			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('id'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('id'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('state'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('state'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('jgroup_id'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('jgroup_id'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('cgroup_id'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('cgroup_id'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('created_by'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('created_by'); ?></div>
			</div>
            

                </fieldset>
            </div>
        </div>
        <?php echo JHtml::_('bootstrap.endTab'); ?>
            

            
        <?php echo JHtml::_('bootstrap.endTabSet'); ?>

        <input type="hidden" name="task" value="" />
        <?php echo JHtml::_('form.token'); ?>

	</div>
</form>
