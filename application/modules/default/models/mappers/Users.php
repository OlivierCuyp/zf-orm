<?php
class Model_Mapper_Users extends Model_Mapper_Abstract {
	protected $_tableGatewayClass = 'Model_DbTable_Users';
	protected $_entityClass = 'Model_User';
	protected $_formClass = 'Admin_Form_User';
	
	protected $_DependenciesMap = array(
		'post' => array(
			'mapperClass' => 'Model_Mapper_Post',
			'mapper' => null,
			'method' => 'fetchByAuthor',
			// Since all the posts of the users might be bing and not necessary
			'earlyLoad' => false
		)
	);
	
	// Redefine fetchParams to set a default order
	protected $_fetchParams = array(
		'order' => 'email asc',
		'limit' => null,
		'page' => null
	);
	
	protected $_fetchOptions = array(
    	'email' => null,
		'idPost' => null
	);
	
	protected function _manageFetchOptions($select, $options) {
		if($options['email'] !== null) {
			$select->where('labels_keywords.text = ?', $options['email']);
		}
    	if($options['idPost'] !== null) {
        	$select->where('idPost = ?', $options['idPost']);	
        }
	}
	
	public function findByEmail($email) {
		$entity = null;
		
		$results = $this->fetchAll(array('email' => $email));
		if (count($results)) {
			$entity = $results[0];
		}
		
		return $entity;
	}
	
    public function fetchByPost($idPost, $options = array(), $params = array()) {
    	$options['idPost'] = $idPost;
    	return $this->fetchAll($options, $params);
    }
}