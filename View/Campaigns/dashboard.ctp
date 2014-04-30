<?php
#debug($this->request->data);
$partner = $this->Session->read('Auth.User');
#debug($partner);
$latestActivity = Hash::extract($this->request->data, "{n}.CampaignResults");
$results = array();
foreach ($latestActivity as $campaignResult) {
	if (!empty($campaignResult)) {
		$results = Hash::merge($results, $campaignResult);
	}
}
usort($results, function($a, $b) {
    return strtotime($a['modified']) < strtotime($b['modified']);
});
$latestActivity = $results;
?>
<div class="row well">
	<div class="col-md-1">[image]</div>
	<div class="col-md-6">
		<div class="row">
			<div class="col-md-12"><h3><?php echo $partner['full_name'] ?></h3></div>
		</div>
		<div class="row">
			<div class="col-md-6"><?php echo $partner['full_name'] ?></div>
			<div class="col-md-6">Partner since: <?php echo date('m/d/Y', $partner['created']) ?></div>
		</div>
	</div>
	<div class="col-md-1 col-md-offset-3">Status: [status]</div>
</div>

<h4>Dashboard</h4>

	<?php if(!empty($this->request->data)): ?>
	<table class="table">
		<thead>
			<th>Location</th>
			<th class="text-center">Avg. Gifty</th>
			<th class="text-center">Shared</th>
			<th class="text-center">Shared ($)</th>
			<th class="text-center">Used</th>
			<th class="text-center">Used ($)</th>
		</thead>
		<?php foreach($this->request->data as $campaign): ?>
			<?php
			$campaignNames[$campaign['Campaign']['id']] = $campaign['Campaign']['name'];
			$campaignOwners[$campaign['Campaign']['id']] = $campaign['Campaign']['owner_id'];
			if (!empty($campaign['CampaignResults'])) {
				foreach ($campaign['CampaignResults'] as $campaignResult) {
					$campaignResultValues[$campaignResult['campaign_id']][] = $campaignResult['coupon_value'];
					if ($campaignResult['status'] == STATUS_USED) {
						$usedVouchers[$campaignResult['campaign_id']][] = $campaignResult['coupon_value'];
					}
				}
			} else {
				$campaignResultValues[$campaign['Campaign']['id']][] = 0;
			}
			if (!isset($usedVouchers[$campaign['Campaign']['id']])) {
				$usedVouchers[$campaign['Campaign']['id']] = false;
			}
			?>

		<tr class="well">
			<td><?php echo $campaign['Campaign']['name'] ?></td>
			<td class="text-center">$<?php echo number_format($campaign['Campaign']['data']['max'], 2) ?></td>
			<td class="text-center"><?php echo count($campaign['CampaignResults']) ?></td>
			<td class="text-center">$<?php echo number_format(array_sum($campaignResultValues[$campaign['Campaign']['id']]), 2) ?></td>
			<td class="text-center"><?php echo ($usedVouchers[$campaign['Campaign']['id']]) ? count($usedVouchers[$campaign['Campaign']['id']]) : '0' ?></td>
			<td class="text-center">$<?php echo ($usedVouchers[$campaign['Campaign']['id']]) ? number_format(array_sum($usedVouchers[$campaign['Campaign']['id']]), 2) : '0' ?></td>
		</tr>

		<?php endforeach; ?>
	</table>
	<button type="button" class="btn btn-primary pull-right" id="stopCampaign">
		Stop Campaign
	</button>
	<?php else: ?>
		<h4>No Results Found</h4>
	<?php endif; ?>


<hr />
<h4>Recent Activity</h4>
	<?php if(!empty($latestActivity)): ?>
	<table class="table">
		<thead>
			<th>Campaign Name</th>
			<th class="text-center">Action</th>
			<th class="text-center">Amount</th>
			<th class="text-center">Gifty ID</th>
			<th class="text-center">Date &amp; Time</th>
		</thead>
		<?php foreach($latestActivity as $activity): ?>
		<?php
				switch ($activity['status']) {
					case (STATUS_PENDING):
						$status = 'Pending';
						break;
					case (STATUS_SHARED):
						$status = 'Shared';
						break;
					case (STATUS_USABLE):
						$status = 'Earned';
						break;
					case (STATUS_USED):
						$status = 'Redeemed';
						break;
				}

		?>
		<tr class="well">
			<td><?php echo $campaignNames[$activity['campaign_id']] ?></td>
			<td class="text-center"><?php echo $status ?></td>
			<td class="text-center">$<?php echo number_format($activity['coupon_value'], 2) ?></td>
			<td class="text-center"><?php echo $this->Campaign->getVoucherCode(array(
				'campaignOwnerId' => $campaignOwners[$activity['campaign_id']],
				'campaignId' => $activity['campaign_id'],
				'campaignResultId' => $activity['id']
			)) ?></td>
			<td class="text-center"><?php echo $this->Time->niceShort($activity['modified']) ?></td>
		</tr>
		<?php endforeach; ?>
	</table>
	<?php endif; ?>

<hr />
<h4>Payment</h4>
<?php if(!empty($this->request->data)): ?>
	<?php foreach($this->request->data as $campaign): ?>
		<?php
		$shareCost = count($campaign['CampaignResults']) * .5;
		$redemptionCost = ($usedVouchers[$campaign['Campaign']['id']]) ? count($usedVouchers[$campaign['Campaign']['id']]) * 1 : 0;
		?>
<div>
	<div><b><?php echo $campaign['Campaign']['name'] ?></b></div>
	<div class="row well">
		<div class="col-md-5">
			<div class="row">
				<div class="col-md-6 text-right">
					Outstanding<br />Shares
				</div>
				<div class="col-md-6 text-center">
					<div style="font-size: 36px;"><?php echo count($campaign['CampaignResults']) ?></div>
					<div>$0.50/Share</div>
				</div>
			</div>
		</div>
		<div class="col-md-5">
			<div class="row">
				<div class="col-md-6 text-right">
					Outstanding<br />Redemptions
				</div>
				<div class="col-md-6 text-center">
					<div style="font-size: 36px;"><?php echo ($usedVouchers[$campaign['Campaign']['id']]) ? count($usedVouchers[$campaign['Campaign']['id']]) : '0' ?></div>
					<div>$1.00/Redemption</div>
				</div>
			</div>
		</div>
		<div class="col-md-2">
			<b>Total Due <span style="font-size: 36px;">$<?php echo number_format($shareCost + $redemptionCost, 2) ?> </span></b>
		</div>
	</div>
</div>
	<?php endforeach; ?>
<?php endif; ?>


<script type="text/javascript">
	$('button#stopCampaign')
			.popover({
				placement: 'left',
				html: true,
				title: 'Confirmation',
				content: '<center><p>Once you stop your campaign, you have to contact us to restart!</p><br />'
				+ '<a href="/campaigns/campaigns/stop" class="btn btn-primary">Stop Campaign</a><br /></center>'
			})
			.on('show.bs.popover', function(){
				$('button#stopCampaign').html('Cancel');
			})
			.on('hide.bs.popover', function(){
				$('button#stopCampaign').html('Stop Campaign');
			});
</script>


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

