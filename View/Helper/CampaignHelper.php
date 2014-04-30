<?php

class CampaignHelper extends AppHelper {
/**
 * determine voucher code
 * owners initials, merchant number (order each merchant signed up) and voucher number.
 * Example owner: Bruce Wayne, 5th merchant to sign on, voucher number: 3. BW5-3
 *
 * @param array $params ('campaignOwnerId', 'campaignResultId', 'campaignId')
 * @return string
 */
	public function getVoucherCode($params) {
		$this->Campaign = ClassRegistry::init('Campaigns.Campaign');
		$merchants = $this->Campaign->Owner->query(""
				. "SELECT"
				. " @rownum:=@rownum+1 'rank', Owner.id, Owner.created, Owner.first_name, Owner.last_name"
				. " FROM `users` AS Owner, (SELECT @rownum:=0) r"
				. " WHERE Owner.user_role_id = '6'"
				. " ORDER BY created ASC;");
		foreach ($merchants as $merchant) {
			if ($merchant['Owner']['id'] === $params['campaignOwnerId']) {
				$merchantNumber = $merchant[0]['rank'];
				$ownersInitials = $merchant['Owner']['first_name'][0] . $merchant['Owner']['last_name'][0];
				break;
			}
		}
		if ($merchantNumber === null) {
			$merchantNumber = 0; // campaign owner is not a Merchant user_role.  Likely is the admin.
		}

		$vouchers = $this->Campaign->CampaignResults->query(""
				. "SELECT"
				. " @rownum:=@rownum+1 'rank', CampaignResult.id, CampaignResult.created"
				. " FROM `campaign_results` AS CampaignResult, (SELECT @rownum:=0) r"
				. " WHERE CampaignResult.campaign_id = '{$params['campaignId']}'"
				. " ORDER BY created ASC;");
		foreach ($vouchers as $voucher) {
			if ($voucher['CampaignResult']['id'] === $params['campaignResultId']) {
				$voucherNumber = $voucher[0]['rank'];
				break;
			}
		}

		$voucherCode = $ownersInitials . $merchantNumber . '-' . $voucherNumber;

		return $voucherCode;
	}
}
