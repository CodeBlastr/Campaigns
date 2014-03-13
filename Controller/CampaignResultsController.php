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
 var $components = array('Auth'=>array('loginRedirect' => array('controller' => 'campaign_results', 'action' => 'claim')), 'Facebook.Connect');
 public $allowedActions = array('result','claim','vouchers','redemption','redemption_swipe','redemption_swipe_confirm');
 	 
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
		$count = $this->CampaignResult->find('count', array('conditions'=>array('creator_id'=>$user_id, 'campaign_id'=>$campaign_id)));
		if(!$count)	{
			$data['CampaignResult'] = array('creator_id'=>$user_id, 'campaign_id'=>$campaign_id, 'coupon_value'=>$result);
			$this->CampaignResult->save($data);
			$response['status']='saved';
		}	else	{
			$response['status']='exists';
		}
		header("Content-type: application/json");
		echo json_encode($response);
		exit;
	}
	
	/**
 * index method
 * This method is used to show the share page after user has spinned the wheel, may be there need to add a page between the spin page and results/share page
 * @return void
 */
	
	public function result($campaign_id=null)	{
		$upload_dir = ROOT.DS.SITE_DIR.DS.'/Locale/View/webroot/tmp';
		
		App::import('Lib', 'Facebook.FB');		
		$FB = new FB();
		
		//$ret_obj = $FB->api("/me/friends");
		//$ret_obj = $FB->api('/me/friendlists');
		
		//$friendList = json_decode($ret_obj);
		
		//debug($ret_obj);
		
		if($this->request->is('post'))	{
			$id = $this->request->data['CampaignResult']['id'];
			$feed = array(
					'link' => Router::url(array('controller'=>'campaign_results', 'action'=>'result', $id), true),
					'message' => 'I would like to share a free coupon with you!'
			 );
			if($this->request->data['CampaignResult']['imagefile']['error']==0)	{
				//pr("uploading");
				if(move_uploaded_file($this->request->data['CampaignResult']['imagefile']['tmp_name'], $upload_dir . DS . $this->request->data['CampaignResult']['imagefile']['name']))	{
					$picture = Router::url('/tmp/' . $this->request->data['CampaignResult']['imagefile']['name'], true);
				}
			}
			if($picture)	{
				$feed['picture'] = $picture;
			}
			//pr($feed);
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
		
		//echo $facebook_id;
		
		$user_id = $this->Session->read('Auth.User.id');
		$this->CampaignResult->contain(array('Campaign', 'Creator'));
		$campaign_result = $this->CampaignResult->find('first', array('conditions'=>array('CampaignResult.creator_id'=>$user_id, 'CampaignResult.campaign_id'=>$campaign_id)));
		//debug($campaign_result);
		$this->set(compact('campaign_result', 'facebook_id'));
	}
	
/**
 * claim method
 * This method is supposed to be clicked by a facebook user. This link is sent to user's fb message box as a gift coupon and user click on this link to claim their reward. 
 
 Completing the redemption by user a gift coupon and reward points or both are awarded to the sender. This link can be clicked by multiple users so this need to be coded in a manner so that one user can redeem their coupon only once. Probaly need to track the fb user id when creating new account for them.
 
 Also this method is called by the facebook API Open Graph Object url parse. So need to differentiate the call. Using HTTP_USER_AGENT to identify the call from FB
 * @return void
 */
	
	function claim($campaign_result_id_rw=''){ //	
		
		//debug($this->Auth->loginRedirect);
		//exit;
		//print_r($_SERVER);
		
						//debug($this->CampaignResult->find('all'));
					//exit;
		
		//$agent_facebook = false;
		
		if(strstr($_SERVER['HTTP_USER_AGENT'], 'facebookexternalhit'))	{
			$agent_facebook = true;
		}
		//print_r($_REQUEST);		
		//exit;		
		//debug($this->CampaignResult->query('delete from campaign_results where 1=1'));
		
		//$r = $this->CampaignResult->query("select * from campaign_invites");
		//$this->CampaignResult->query("drop table zbk_campaign_results");
		//$this->CampaignResult->query("drop table zbk_campaigns");		
		//$campaign_result_id='530c3cf1-48e0-489c-a502-73240ad25527';
		$campaign_result_id= base64_decode($campaign_result_id_rw); //this id was encoded to avoid the error in FB dialog API
		$this->CampaignResult->contain(array('Campaign', 'Creator'));
		$this->CampaignResult->id = $campaign_result_id;
		$campaign_result = $this->CampaignResult->read();
		
		if(!$campaign_result)	{
			die('No such compaign');
		}
		
		$sender_id = $campaign_result['CampaignResult']['creator_id']; //it is creator_id who shared it. We are updating the compaign result after user has save using ajax(Todo) so it's better we pick creator_id who is the sender in most cases.
		
		//debug($campaign_result);
		//$this->Session->write('Campaign.campaign_claim_id', $campaign_result_id);
		$fbmetas = 'true';
		
		if(!$agent_facebook)	{
			$this->Session->write('Campaign.campaign_claim_id', $campaign_result_id);
			if($campaign_result)	{
				if(!$this->Auth->loggedIn())	{
					$this->Session->write('Campaign.campaign_claim_id', $campaign_result_id);
					$this->Auth->loginRedirect = array('controller' => 'campaign_results', 'action' => 'claim', $campaign_result_id_rw);
					$this->redirect(array('controller'=>'users', 'plugin'=>'users', 'action'=>'login'));
				}
				$user_id = $this->Session->read('Auth.User.id');
						
				/*if($sender_id==$user_id)	{
					die('Cannot claim your own gift coupon');
				}*/
				
				if($this->Connect->user())	{ //facebook check user
					$this->FB = $this->Connect->user();
					$facebook_id = $this->FB['id'];
				}
				//$this->loadModel('Campaigns.CampaignInvite');
				
				//debug($this->CampaignResult->find('all'));
				//	exit;
				
				//debug($this->CampaignInvite->query('delete from campaign_invites where 1=1'));
				if($this->Session->read('Campaign.campaign_claim_id'))	{
					$data = array('parent_id'=>$campaign_result_id, 'sender_id'=>$campaign_result['CampaignResult']['creator_id'], 'recepient_id'=>$user_id, 'status'=>1, 'recepient_fbid'=>$facebook_id, 'coupon_value'=>$campaign_result['CampaignResult']['coupon_value']); //status 1 means this coupon is ready to use.
					
					if($facebook_id)	{					
						$conditions['recepient_fbid'] = $facebook_id; //it's better to find with fb it and there is chance that user may try to click and receive a coupon multiple times.
					}	else	{
						$conditions['recepient_id'] = $user_id;
					}
					$conditions['parent_id'] = $campaign_result_id;
					
					$first = $this->CampaignResult->find('first', array('conditions'=>$conditions));
				
					if(!$first)	{
						//debug($data);
						$this->CampaignResult->create();
						$saved = $this->CampaignResult->save($data);
						/*if($facebook_id)	{
							App::import('Lib', 'Facebook.FB');		
							$FB = new FB();
							$ret_obj = $FB->api('/me/feed', 'POST',
								array(
											'link' => 'http://sharendipity.buildrr.com/',
											'message' => 'I redeemed a gift coupon worth $'.$campaign_result['CampaignResult']['result'].'!',
											'picture' => Router::url('/img/big-shoulders.jpg', true)
								 ));							
							//debug($ret_obj);
						}*/
						$this->Session->delete('Campaign.campaign_claim_id');
						$redeemed_thankyou = true;
					}	else	{
						$redeemed_thankyou = true;
						$already_redeemed = true;
					}
				}
			}	
		}
		
			//$this->Campaign->
			
			//$this->set('campaign', $campaign = $this->Campaign->read());
			$campaign = $campaign_result;
			//debug($campaign_result);
			$meta_description = $campaign_result['Campaign']['description'];		
			//$this->page_title = $campaign_result['Campaign']['name'];
			$this->set('title_for_layout', $campaign_result['Campaign']['name']);
			//debug($campaign_result);
		
		$this->set(compact('campaign_result', 'facebook_id', 'meta_description', 'fbmetas', 'already_redeemed', 'campaign', 'already_redeemed'));
		
		if(isset($redeemed_thankyou))	{
			$this->render('claim_thankyou');
		}
	}
	
	public function giftcouponshared() {
		
	}
	
	public function vouchers($action='') {

		$user_id = $this->Session->read('Auth.User.id');
		
		if(!$action) $action='shared';
		
		switch($action)	{
			case 'shared':
				$conditions = array('CampaignResult.creator_id'=>$user_id);
			break;
			case 'received':
				$conditions = array('CampaignResult.recepient_id'=>$user_id, 'CampaignResult.status'=>array(1,2)); 
			break;
		}
		
		//$this->CampaignResult->recursive = 2;
		$this->CampaignResult->contain(array('Campaign','Recepient','Creator'));
		//$this->CampaignResult->Campaign->contain(array('Owner'));
		$vouchers = $this->CampaignResult->find('all', array('conditions'=>$conditions));
		
		
		//debug($vouchers);
		
		$this->set(compact('vouchers', 'action'));
		
		//$conditions['CampaignResult.user_id'] = 
		
		//$campaign_result = $this->CampaignResult->find('all', array('conditions'=>$conditions));
		
	}
        
        public function redemption($coupan_id) {
		$user_id = $this->Session->read('Auth.User.id');
		$conditions = array('CampaignResult.id'=>$coupan_id,'CampaignResult.recepient_id'=>$user_id,'CampaignResult.status'=>1);
		
		//$this->CampaignResult->recursive = 2;
		$this->CampaignResult->contain(array('Campaign','Recepient'));
		//$this->CampaignResult->Campaign->contain(array('Owner'));
		$redemption = $this->CampaignResult->find('all', array('conditions'=>$conditions));
		
		
		//debug($vouchers);
		
		$this->set(compact('redemption'));
		
		//$conditions['CampaignResult.user_id'] = 
		
		//$campaign_result = $this->CampaignResult->find('all', array('conditions'=>$conditions));
		
	}
        
       public function redemption_swipe($coupan_id) {
		$user_id = $this->Session->read('Auth.User.id');
		$conditions = array('CampaignResult.id'=>$coupan_id,'CampaignResult.recepient_id'=>$user_id,'CampaignResult.status'=>1);
		$this->CampaignResult->contain(array('Campaign','Recepient'));
		$redemption = $this->CampaignResult->find('all', array('conditions'=>$conditions));
		$this->set(compact('redemption'));
	}
        public function redemption_swipe_confirm($coupan_id) {
		$user_id = $this->Session->read('Auth.User.id');
		if($user_id)	{
			$this->CampaignResult->id = $coupan_id;
			$data = array('status'=>2);
			$this->CampaignResult->save($data);
                        
                        $this->CampaignResult->id = $campaign_result_id;
                        $campaign = $this->CampaignResult->read();
                        $this->set(compact('campaign'));
                        $this->render('redemption_swipe_thankyou');
		}
	}
        
	public function update($campaign_result_id, $status) {
		$user_id = $this->Session->read('Auth.User.id');
		if($user_id)	{
			$this->CampaignResult->id = $campaign_result_id;
			$data = array('status'=>0);
			$this->CampaignResult->save($data);
			exit;
		}
	}

}
