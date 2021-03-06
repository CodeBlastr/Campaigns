<?php
App::uses('CampaignsAppModel', 'Campaigns.Model');
/**
 * Campaign Model
 *
 * Extension Code
 * $refuseInit = true; require_once(ROOT.DS.'app'.DS.'Plugin'.DS.'Blogs'.DS.'Model'.DS.'BlogPost.php');
 * 
 * @property Owner $Owner
 */

class AppCampaign extends CampaignsAppModel {

	public $name = 'Campaign';
/**
 * Display field
 *
 * @var string
 */
	public $displayField = 'name';

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'name' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		)
	);

	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'Owner' => array(
			'className' => 'Users.User',
			'foreignKey' => 'owner_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

	public $hasMany = array(
		'CampaignResults'
	);

/**
 * Constructor
 *
 */
	public function __construct($id = false, $table = null, $ds = null) {
		if (CakePlugin::loaded('Maps')) {
			$this->actsAs['Maps.Mapable'] = array(
				'modelAlias' => 'Campaign',
				'markerTextField' => 'description',
				'streetField' => 'address_1',
				'cityField' => 'city',
				'stateField' => 'state',
				'countryField' => null,
				'postalField' => 'zip',
				'addressField' => array('address_1', 'address_2', 'city', 'state', 'zip'),
				'markerTextField' => 'description',
				'searchTagsField' => 'description'
			);
		}
		if (CakePlugin::loaded('Categories')) {			
			$this->actsAs['Categories.Categorizable'] = array('modelAlias' => 'Campaign');
		}
		// Media added for one site ^JB
		if (CakePlugin::loaded('Media')) {
			$this->actsAs[] = 'Media.MediaAttachable';
		}
		parent::__construct($id, $table, $ds);
	}

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
	public function afterFind($results = array(), $primary = false) {
		if ($primary) {
			for ($i=0; $i < count($results); $i++) {
				$results[$i][$this->alias]['data'] = unserialize($results[$i][$this->alias]['data']);
				if(isset($results[$i][$this->alias]['data']['max'])) {
					$results[$i][$this->alias]['data']['max'] = $results[$i][$this->alias]['data']['max'] + 0;
				}
			}
		}
		return parent::afterFind($results, $primary);
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

if (!isset($refuseInit)) {
	class Campaign extends AppCampaign {}
}
