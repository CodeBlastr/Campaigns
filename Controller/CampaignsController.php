<?php

App::uses('CampaignsAppController', 'Campaigns.Controller');

/**
 * Campaigns Controller
 *
 * @property Campaign $Campaign
 */
class AppCampaignsController extends CampaignsAppController {

	public $uses = 'Campaigns.Campaign';

/**
 *
 * @return void
 */
	public function index() {
		$this->set('campaigns', $campaigns = $this->paginate());
	}

/**
 * for merchants to see their campaigns
 */
	public function dashboard() {
		$this->request->data = $this->Campaign->find('all', array(
			'conditions' => array(
				'owner_id' => $this->Session->read('Auth.User.id')
			),
			'contain' => array(
				'CampaignResults' => array(
					'order' => array('CampaignResults.modified' => 'DESC')
				)
			)
		));
	}

/**
 * seems to update the status of your vouchers
 */
	function __processVouchers() {
		//if($this->Session->read('Facebook.refresh_vouchers')) return;
		$user_id = $this->Session->read('Auth.User.id');
		if ($this->Connect->user()) { //facebook check user
			$this->FB = $this->Connect->user();
			$facebook_id = $this->FB['id'];
		}
		$pending_vouchers = $this->CampaignResult->find('all', array('conditions' => array('CampaignResult.recepient_fbid' => $facebook_id, 'CampaignResult.status' => array(STATUS_PENDING, STATUS_SHARED))));
		if (count($pending_vouchers) > 0) {
			foreach ($pending_vouchers as $voucher) {
				$this->CampaignResult->id = $voucher['CampaignResult']['id'];
				$this->CampaignResult->save(array('recepient_id' => $user_id, 'status' => STATUS_USABLE));
				//Make the parent voucher as "Available" for the sharer
				if (!is_null($voucher['CampaignResult']['parent_id'])) {
					$this->CampaignResult->id = $voucher['CampaignResult']['parent_id'];
					$this->CampaignResult->save(array('status' => STATUS_USABLE, 'sender_id' => $user_id));
				}
			}
		}
		$this->Session->write('Facebook.refresh_vouchers', true);
	}

/**
 *
 * @return void
 */
	public function nearby($lat = '41.8826374', $long = '-87.6239217', $radius = 1) {
		App::uses('Map', 'Maps.Model');
		$Map = new Map();
		$locations = Set::extract('/Map/foreign_key', $Map->findLocation($lat, $long, $radius));
		//$locations = array('52fb5844-06b4-4a6f-815b-49fd0ad25527', '52fb5867-3dd8-49e0-ad7a-21460ad25527');
		$this->paginate['conditions']['Campaign.id'] = $locations;
		$campaigns = $this->paginate();
		if (!count($campaigns)) {
			$this->Session->setFlash(__('No Nearby Locations Found'));
			$this->redirect('/users/users/my');
		}

		$this->loadModel('Campaigns.CampaignResult');

		$this->__processVouchers();

		foreach ($campaigns as $i => $campaign) {
			$campaign_id = $campaign['Campaign']['id'];
			$count = $this->CampaignResult->find('count', array('conditions' => array('CampaignResult.campaign_id' => $campaign_id, 'CampaignResult.status >' => STATUS_PENDING)));
			$campaigns[$i]['Campaign']['shared_count'] = $count;
		}

		$this->set('campaigns', $campaigns);
		$this->set(compact('lat', 'long', 'radius'));
		if (!count($campaigns) > 0) {
			$this->render('nonearbycampaigns');
		}
	}

/**
 *
 * @return void
 */
	public function my() {
		$this->paginate['conditions']['Campaign.owner_id'] = $this->Session->read('Auth.User.id');
		$this->set('campaigns', $campaigns = $this->paginate());
		$this->view = 'index';
	}

/**
 *
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		$this->Campaign->id = $id;
		if (!$this->Campaign->exists()) {
			throw new NotFoundException(__('Invalid'));
		}
		$this->Campaign->contain(array('Owner'));
		$this->set('campaign', $campaign = $this->Campaign->read());
	}

/**
 *
 * NOTE : This is specific to sharendipity, not necessary to save, please copy if removing
 * @param string $id
 * @return void
 */
	public function info($id = null) {
		$this->Campaign->id = $id;
		if (!$this->Campaign->exists()) {
			throw new NotFoundException(__('Invalid'));
		}
		$this->Campaign->contain(array('Owner'));
		$this->set('campaign', $campaign = $this->Campaign->read());
	}

/**
 *
 * NOTE : This is specific to sharendipity, not necessary to save, please copy if removing
 * @param string $id
 * @return void
 */
	public function wheel($id = null) {
		$this->Campaign->id = $id;

		if (!$this->Campaign->exists()) {
			throw new NotFoundException(__('Invalid'));
		}
		$this->Campaign->contain(array('Owner'));

		$user_id = $this->Session->read('Auth.User.id');
		$campaign_result = $this->Campaign->CampaignResults->find('first', array('conditions' => array('creator_id' => $user_id, 'campaign_id' => $id, 'status' => STATUS_PENDING, 'parent_id IS NULL')));

		$exists = $campaign_result ? 1 : 0;

		if ($exists) {
			//$this->redirect('/campaigns/campaign_results/result/' . $campaign_result['CampaignResult']['id']);
		}

		$this->set('campaign', $campaign = $this->Campaign->read());
		$this->set(compact('campaign_result', 'exists'));
	}

/**
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->Campaign->create();
			if ($this->Campaign->save($this->request->data)) {
				$this->Session->setFlash(__('Saved'), 'flash_success');
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('Could not be saved. Please, try again.'), 'flash_warning');
			}
		}

		if ($this->request->is('get')) {
			if ($this->Session->read('Auth.User.user_role_id') == '1') {
				$this->set('merchants', $this->Campaign->Owner->find('list', array(
					'conditions' => array('Owner.user_role_id' => '6')
				)));
			}
		}
	}

/**
 *
 * @param string $id
 * @return void
 */
	public function edit($id = null) {
		$this->Campaign->id = $id;
		if (!$this->Campaign->exists()) {
			throw new NotFoundException(__('Invalid'));
		}
		if ($this->request->is('post') || $this->request->is('put')) {
			if ($this->Campaign->save($this->request->data)) {
				$this->Session->setFlash(__('Saved'), 'flash_success');
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('Could not be saved. Please, try again.'), 'flash_warning');
			}
		}
		$this->request->data = $this->Campaign->read(null, $id);
	}

/**
 *
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		if (!$this->request->is('post')) {
			throw new MethodNotAllowedException();
		}
		$this->Campaign->id = $id;
		if (!$this->Campaign->exists()) {
			throw new NotFoundException(__('Invalid'));
		}
		if ($this->Campaign->delete()) {
			$this->Session->setFlash(__('Deleted'), 'flash_success');
			$this->redirect(array('action' => 'index'));
		}
		$this->Session->setFlash(__('Not deleted'), 'flash_warning');
		$this->redirect(array('action' => 'index'));
	}

/**
 * displays an alternate users/view  ... the main on in use on this site
 */
	public function home() {

		$this->layout = 'ajax';

		$this->loadModel('Campaigns.CampaignResult');

		$this->__processVouchers();

		$sharesamount = 0;

		$user = $this->Session->read('Auth.User');

		$usable_count = $this->CampaignResult->find('count', array('conditions' => array('recepient_id' => $user['id'], 'status' => STATUS_USABLE)));

		$sharescount = $this->CampaignResult->find('count', array('conditions' => array('sender_id' => $user['id'])));
		$sharesamount_a = $this->CampaignResult->find('first', array('conditions' => array('sender_id' => $user['id']), 'fields' => array('sum(coupon_value) AS sharesamount')));
		if (!is_null($sharesamount_a[0]['sharesamount'])) {
			$sharesamount = $sharesamount_a[0]['sharesamount'];
		}

		$lat = '41.8826374';
		$long = '-87.6239217';
		$radius = 1;
		App::uses('Map', 'Maps.Model');
		$Map = new Map();
		$locations = Set::extract('/Map/foreign_key', $Map->findLocation($lat, $long, $radius));
		$locations_count = $this->Campaign->find('count', array('conditions' => array('Campaign.id' => $locations)));

		$this->set(compact('user', 'usable_count', 'locations_count', 'sharescount', 'sharesamount'));

		$this->render();
	}

/**
 * 
 * @param string $campaign
 */
	public function stop($campaign = 'my') {
		if ($campaign === 'my') {
			$conditions = array(
				'Campaign.owner_id' => $this->Session->read('Auth.User.id')
			);
		} else {
			$conditions = array(
				'Campaign.id' => $campaign
			);
		}

		$stopped = $this->Campaign->updateAll(array('end' => 'NOW()'), $conditions);
		if ($stopped) {
			$this->Session->setFlash('Your campaign(s) have been stopped.', 'flash_success');
		} else {
			$this->Session->setFlash('Unable to stop your campaign(s).', 'flash_danger');
		}

		$this->redirect($this->referer());
	}

}

if (!isset($refuseInit)) {

	class CampaignsController extends AppCampaignsController {
		
	}

}
