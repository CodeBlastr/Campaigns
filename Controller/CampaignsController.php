<?php
App::uses('CampaignsAppController', 'Campaigns.Controller');
/**
 * Campaigns Controller
 *
 * @property Campaign $Campaign
 */
class AppCampaignsController extends CampaignsAppController {

/**
 * Uses
 *
 * @var array
 */
	public $uses = 'Campaigns.Campaign';

/**
 * index method
 *
 * @return void
 */
	public function index() {
		$this->set('campaigns', $campaigns = $this->paginate());
	}
	
	
	function __processVouchers()	{
		//if($this->Session->read('Facebook.refresh_vouchers')) return;
		$user_id = $this->Session->read('Auth.User.id');
		if ($this->Connect->user())	{ //facebook check user
			$this->FB = $this->Connect->user();
			$facebook_id = $this->FB['id'];
		}
		$pending_vouchers = $this->CampaignResult->find('all', array('conditions'=>array('CampaignResult.recepient_fbid'=>$facebook_id, 'CampaignResult.status'=>STATUS_PENDING)));
		if(count($pending_vouchers)>0)	{
			foreach($pending_vouchers as $voucher)	{
				$this->CampaignResult->id = $voucher['CampaignResult']['id'];
				$this->CampaignResult->save(array('recepient_id'=>$user_id, 'status'=>STATUS_SHARED));
			}
		}
		$this->Session->write('Facebook.refresh_vouchers', true);
	}

/**
 * nearby method
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
		if(!count($campaigns)){
				$this->Session->setFlash(__('No Nearby Locations Found'));
				$this->redirect('/');
		}
		
		$this->loadModel('Campaigns.CampaignResult');
		
		$this->__processVouchers();
		
		foreach($campaigns as $i=>$campaign)	{
			$campaign_id = $campaign['Campaign']['id'];
			$count = $this->CampaignResult->find('count', array('conditions'=>array('CampaignResult.campaign_id'=>$campaign_id, 'CampaignResult.status >'=>STATUS_PENDING)));
			$campaigns[$i]['Campaign']['shared_count'] = $count;
		}
		$this->set('campaigns', $campaigns);
		$this->set(compact('lat', 'long', 'radius'));
	}

/**
 * index method
 *
 * @return void
 */
	public function my() {
		$this->paginate['conditions']['Campaign.owner_id'] = $this->Session->read('Auth.User.id');
		$this->set('campaigns', $campaigns = $this->paginate());
		$this->view = 'index';
	}

/**
 * view method
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
 * info method
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
 * wheel method
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
		
		//App::uses('CampaignResult', 'Campaigns.Model');
		//$CampaignResult = new CampaignResult();
		$this->loadModel('Campaigns.CampaignResult');
		
		//debug($this->CampaignResult->find('all'));
		
		//$this->CampaignResult->query("delete from campaign_results where id='52fcf3f3-1a1c-4768-8aff-3af20ad25527'");		
		$user_id = $this->Session->read('Auth.User.id');
		$campaign_result = $this->CampaignResult->find('first', array('conditions'=>array('creator_id'=>$user_id, 'campaign_id'=>$id, 'status'=>STATUS_PENDING, 'parent_id IS NULL')));
		
		$exists = $campaign_result ? 1 : 0;
		
		$this->set('campaign', $campaign = $this->Campaign->read());
		$this->set(compact('campaign_result', 'exists'));
	}
	

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->Campaign->create();
			if ($this->Campaign->save($this->request->data)) {
				$this->Session->setFlash(__('Saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('Could not be saved. Please, try again.'));
			}
		}
	}

/**
 * edit method
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
				$this->Session->setFlash(__('Saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('Could not be saved. Please, try again.'));
			}
		}
        $this->request->data = $this->Campaign->read(null, $id);
	}

/**
 * delete method
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
			$this->Session->setFlash(__('Deleted'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Session->setFlash(__('Not deleted'));
		$this->redirect(array('action' => 'index'));
	}
	
	public function home()	{
		$this->loadModel('Campaigns.CampaignResult');
		$user_id = $this->Session->read('Auth.User.id');
		$usable_count = $this->CampaignResult->find('count', array('conditions'=>array('recepient_id'=>$user_id, 'status'=>STATUS_USABLE)));

		$lat = '41.8826374'; $long = '-87.6239217'; $radius = 1;
		App::uses('Map', 'Maps.Model');
		$Map = new Map();
		$locations = Set::extract('/Map/foreign_key', $Map->findLocation($lat, $long, $radius));
		
		$locations_count = $this->Campaign->find('count', array('conditions'=>array('Campaign.id'=>$locations)));
		
		//$locations_count = count($locations);
		
		$this->set(compact('usable_count', 'locations_count'));
	}
}

if (!isset($refuseInit)) {
	class CampaignsController extends AppCampaignsController {

	}
}
