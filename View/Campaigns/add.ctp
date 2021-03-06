<div class="campaigns form">
	<?php echo $this->Form->create('Campaign', array('type' => 'file')); ?>
	<?php echo $this->Form->hidden('Campaign.owner_id', array('value' => $__userId)); ?>
	<?php echo $this->Form->input('Campaign.name', array('type' => 'text')); ?>
	<?php echo $this->Form->input('Campaign.description', array('type' => 'text')); ?>
	<?php echo $this->Form->input('Campaign.start', array('label' => 'Start Date', 'type' => 'datetimepicker', 'value' => date('Y-m-d h:i:s', strtotime('+30 days')))); ?>
	<?php echo $this->Form->input('Campaign.expire', array('label' => 'Expiration Date', 'type' => 'datetimepicker', 'value' => date('Y-m-d h:i:s', strtotime('+30 days')))); ?>

	<?php //echo $this->Form->input('Campaign.data.max', array('type' => 'text')); ?>
	<?php echo $this->Form->input('Campaign.data.max', array('options' => averages(), 'label'=>'Average')); ?>

	<hr />

	<?php echo $this->Form->input('Campaign.address_1', array('type' => 'text')); ?>
	<?php echo $this->Form->input('Campaign.city', array('type' => 'text')); ?>
	<?php echo $this->Form->input('Campaign.state', array('options' => states())); ?>
	<?php echo $this->Form->input('Campaign.zip', array('type' => 'text')); ?>

	<?php echo $this->Form->end('Save'); ?>
</div>


<?php
// set the contextual menu items
$this->set('context_menu', array('menus' => array(
    array(
		'heading' => 'Campaigns',
		'items' => array(
			$this->Html->link(__('List'), array('controller' => 'campaigns', 'action' => 'index')),
			)
		),
	)));
