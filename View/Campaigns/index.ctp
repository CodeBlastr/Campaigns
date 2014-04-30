<h1>My Campaigns</h1>
<div class="campaigns index list-group">
	<?php if(!empty($campaigns)): ?>
		<?php foreach($campaigns as $campaign): ?>
		<div class="list-group-item clearfix">
			<div class="col-md-3">
				<h4><?php echo $this->Html->link($campaign['Campaign']['name'], array('action' => 'view', $campaign['Campaign']['id'])); ?></h4>
				<p><?php echo $classified['Classified']['data']; ?></p>
			</div>
			<div class="col-md-3">
				<?php echo $campaign['Campaign']['address_1']; ?><br />
				<?php echo $campaign['Campaign']['city']; ?>, <?php echo $campaign['Campaign']['state']; ?>, <?php echo $campaign['Campaign']['zip']; ?>
			</div>
			<div class="col-md-3">
				Max <?php echo ZuhaInflector::pricify($campaign['Campaign']['data']['max'], array('currency' => 'USD')); ?>
			</div>
			<div class="col-md-3">
				<?php echo $this->Html->link('View', array('action' => 'view', $campaign['Campaign']['id']), array('class' => 'btn btn-xs btn-primary')); ?>
				<?php echo $this->Html->link('Edit', array('action' => 'edit', $campaign['Campaign']['id']), array('class' => 'btn btn-xs btn-primary')); ?>
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
			$this->Html->link(__('Add'), array('admin' => true, 'action' => 'add')),
			)
		),
	)));

