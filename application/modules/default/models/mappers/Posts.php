<?php
class Model_Mapper_Posts extends Model_Mapper_Abstract {
	protected $_tableGatewayClass = 'Model_DbTable_Posts';
	protected $_entityClass = 'Model_Post';
	protected $_formClass = 'Admin_Form_Post';
	
	protected $_referenceMap = array(
		'author' => array(
			'mapperClass' => 'Model_Mapper_Users',
			'mapper' => null,
			'field' => 'idAuthor',
			'earlyLoad' => true
		)
	);
	
	// Redefine fetchParams to set a default order
	protected $_fetchParams = array(
			'order' => 'creationDate desc',
			'limit' => null,
			'page' => null
	);
	
	protected $_DependenciesMap = array(
		'labels' => array(
			'mapperClass' => 'Model_Mapper_Labels',
			'mapper' => null,
			'method' => 'fetchByPost',
			'earlyLoad' => true
		)
	);
	
	protected $_fetchOptions = array(
    	'idUser' => null
	);
	
	protected function _manageFetchOptions($select, $options) {
    	if($options['idAuthor'] !== null) {
        	$select->where('idAuthor = ?', $options['idAuthor']);	
        }
	}
	
    public function fetchByAuthor($idAuthor, $options = array(), $params = array()) {
    	$options['idAuthor'] = $idAuthor;
    	return $this->fetchAll($options, $params);
    }
}