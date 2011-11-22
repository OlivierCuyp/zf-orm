<?php
class Model_Mapper_Labels extends Model_Mapper_Abstract {
	protected $_tableGatewayClass = 'Model_DbTable_Labels';
	protected $_entityClass = 'Model_Label';
	protected $_formClass = 'Admin_Form_Label';
	
	protected $_referenceMap = array(
		'label' => array(
			'mapperClass' => 'Model_Mapper_Labels',
			'mapper' => null,
			'field' => 'idLabel',
			'earlyLoad' => true
		)
	);
	
	protected $_fetchOptions = array(
    	'idPost' => null,
		'text' => null
	);
	
	protected function _manageFetchOptions($select, $options) {
    	if($options['idPost'] !== null) {
        	$select->joinInner('posts_labels', 'posts_labels.idLabel = labels.id', array()) // empty array means, we don't take any field from the joined table
        		->where('idPost = ?', $options['idPost']);	
        }
		if($options['text'] !== null) {
			$select->where('text LIKE ?', $options['text']);
        }
	}
	
	public function fetchByPost($idPost, $options = array(), $params = array()) {
    	$options['idPost'] = $idPost;
    	return $this->fetchAll($options, $params);
    }
}