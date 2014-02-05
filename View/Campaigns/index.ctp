<div class="campaigns index">
	<?php if(!empty($campaigns)): ?>
		<?php foreach($campaigns as $campaign): ?>
		<div class="row">
			<div class="col-md-3">
				<h4><?php echo $this->Html->link($campaign['Campaign']['name'], array('action' => 'view', $campaign['Campaign']['id'])); ?></h4>
				<p><?php echo $campaign['Campaign']['description']; ?></p>
				<p><?php echo $classified['Classified']['data']; ?></p>
			</div>
		</div>	
		<?php endforeach; ?>
	<?php else: ?>
		<h4>No Results Found</h4>
	<?php endif; ?>
</div>

<?php echo $this->element('paging'); ?>

<?php
// set the contextual menu items
$this->set('context_menu', array('menus' => array(
    array(
		'heading' => 'Campaigns',
		'items' => array(
			$this->Html->link(__('Add'), array('admin' => true, 'controller' => 'classifieds', 'action' => 'add')),
			)
		),
	)));

