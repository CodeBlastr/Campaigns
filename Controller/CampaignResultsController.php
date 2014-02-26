<?php
App::uses('CampaignsAppController', 'Campaigns.Controller');
/**
 * CampaignResults Controller
 *
 * @property CampaignResults $CampaignResults
 */
class CampaignResultsController extends CampaignsAppController {

/**
 * Uses
 *
 * @var array
 */
	public $uses = 'Campaigns.CampaignResult';
	
/**
 * Components
 *
 * @var array
 */
 var $components = array('Auth', 'Facebook.Connect');
 public $allowedActions = array('result','claim');
 	 
/**
 * index method
 *
 * @return void
 */
	public function index() {
		$this->set('campaignResults', $campaignResults = $this->paginate());
	}
	//9459322447
	public function edit($campaign_id, $result)	{
		$user_id = $this->Session->read('Auth.User.id');
		$response=array();	
		$count = $this->CampaignResult->find('count', array('conditions'=>array('user_id'=>$user_id, 'campaign_id'=>$id)));
		if(!$count)	{
			$data['CampaignResult'] = array('user_id'=>$user_id, 'campaign_id'=>$campaign_id, 'result'=>$result);
			$this->CampaignResult->save($data);
			$response['status']='saved';
		}	else	{
			$response['status']='exists';
		}
		header("Content-type: application/json");
		echo json_encode($response);
		exit;
	}
	
	public function result($campaign_id=null)	{
		$upload_dir = ROOT.DS.SITE_DIR.DS.'/Locale/View/webroot/tmp';
		
		App::import('Lib', 'Facebook.FB');		
		$FB = new FB();
		
		$ret_obj = $FB->api("/me/friends");
		//$ret_obj = $FB->api('/me/friendlists');
		
		//$friendList = json_decode($ret_obj);
		
		//debug($ret_obj);
		
		if($this->request->is('post'))	{
			//pr($this->request->data);
			$id = $this->request->data['CampaignResult']['id'];
			//pr($id);
			$feed = array(
					'link' => Router::url(array('controller'=>'campaign_results', 'action'=>'result', $id), true),
					'message' => 'I would like to share a free coupon with you!'
			 );
			//pr(WWWROOT);
			//pr($this->request->data['CampaignResult']['imagefile']['error']);
			if($this->request->data['CampaignResult']['imagefile']['error']==0)	{
				//pr("uploading");
				if(move_uploaded_file($this->request->data['CampaignResult']['imagefile']['tmp_name'], $upload_dir . DS . $this->request->data['CampaignResult']['imagefile']['name']))	{
					$picture = Router::url('/tmp/' . $this->request->data['CampaignResult']['imagefile']['name'], true);
				}
			}
			if($picture)	{
				$feed['picture'] = $picture;
			}
			pr($feed);
		}
		

		//$FB = FB::api('/me');
		//$FBME = $FB->api('/me');
		
		//debug($FBME);
		//$FacebookApi = new FB();
		//$FBME = $FacebookApi->api('/me/photos');
		//debug($FB);
		
		/*graph.facebook.com
  /{user-id}/feed?
    message={message}&
    access_token={access-token}*/
		
    //$ret_obj = $FB->api('/me/friendlists');
    
		/*
		$ret_obj = $FB->api('/100004388857579/feed', 'POST',
				array(
					'link' => 'http://sharendipity.buildrr.com/',
					'message' => 'Posting with the PHP SDK!'
		 ));*/
		 
		//$ret_obj = $FB->api("/me/friends");
		
		//$friendList = json_decode($ret_obj);
		
		//debug($ret_obj);
		 
		//debug($ret_obj);
     //echo '<pre>Post ID: ' . $ret_obj['id'] . '</pre>';
		
		//debug($this->CampaignResult->query('SHOW COLUMNS FROM campaign_results'));
		//debug($this->CampaignResult->query("delete from campaign_results where id='52fcd653-a7c8-43b9-befd-21460ad25527'"));
		
		//$data['CampaignResult'] = array('user_id'=>1, 'campaign_id'=>'52fb5844-06b4-4a6f-815b-49fd0ad25527', 'result'=>'10');
		//$this->CampaignResult->save($data);
		//debug($this->Connect->user());
		if($this->Connect->user())	{ //facebook check user
					$this->FB = $this->Connect->user();
					//debug($this->FB);
					$facebook_id = $this->FB['id'];
					//debug($this->facebookUser);
		}
		
		$user_id = $this->Session->read('Auth.User.id');
		$this->CampaignResult->contain(array('Campaign', 'User'));
		$campaign_result = $this->CampaignResult->find('first', array('conditions'=>array('user_id'=>$user_id, 'campaign_id'=>$campaign_id)));
		//debug($campaign_result);
		$this->set(compact('campaign_result', 'facebook_id'));
	}
	
	function claim($campaign_result_id='')	{
		$campaign_result_id='530c3cf1-48e0-489c-a502-73240ad25527';
		$this->CampaignResult->contain(array('Campaign', 'User'));
		$this->CampaignResult->id = $campaign_result_id;
		$campaign_result = $this->CampaignResult->read();
		//debug($campaign_result);
		$meta_description = $campaign_result['Campaign']['description'];		
		//$this->page_title = $campaign_result['Campaign']['name'];
		$this->set('title_for_layout', $campaign_result['Campaign']['name']);
		
		$this->set(compact('campaign_result', 'facebook_id', 'meta_description'));
	}
}
