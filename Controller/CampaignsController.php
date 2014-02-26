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
		$this->set('campaigns', $campaigns = $this->paginate());
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
		$exists = $this->CampaignResult->find('count', array('conditions'=>array('user_id'=>$user_id, 'campaign_id'=>$id)));
		//debug($exists);
		$this->set('campaign', $campaign = $this->Campaign->read());
		$this->set(compact('exists'));
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
}

if (!isset($refuseInit)) {
	class CampaignsController extends AppCampaignsController {

	}
}
