<?php
App::uses('CampaignsAppModel', 'Campaigns.Model');
/**
 * CampaignResult Model
 *
 * @property Owner $Owner
 */
class CampaignResult extends CampaignsAppModel {
		
	public $name = 'CampaignResult';


	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'Campaign' => array(
			'className' => 'Campaigns.Campaign',
			'foreignKey' => 'campaign_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'User'
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
 * After Find method
 *
 * @return array
 */
	/*public function afterFind($results = array(), $primary = false) {
		for ($i=0; $i < count($results); $i++) {
			$results[$i][$this->alias]['data'] = unserialize($results[$i][$this->alias]['data']);
		}
		return parent::afterFind($results, $primary);
	}*/

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
