<?php
App::uses('CampaignsAppModel', 'Campaigns.Model');
/**
 * CampaignInvite Model
 *
 * @property Owner $Owner
 */
class CampaignInvite extends CampaignsAppModel {
		
	public $name = 'CampaignInvite';


	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'CampaignResult' => array(
			'className' => 'Campaigns.CampaignResult',
			'foreignKey' => 'campaign_result_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);
	
/**
 * Constructor
 * 
 */
	// public function __construct($id = false, $table = null, $ds = null) {
		// parent::__construct($id, $table, $ds);
	// }

/**
 * Before Save method
 *
 * @param type $options
 * @return boolean
 */
	public function beforeSave($options = array()) {
		$this->data = $this->_cleanData($this->data);
		return parent::beforeSave($options);
	}

/**
 * Clean Data method
 *
 * @param array
 */
	public function _cleanData($data) {
		if (!empty($data[$this->alias]['data'])) {
			$data[$this->alias]['data'] = serialize($data[$this->alias]['data']);
		}
		return $data;
	}

}
