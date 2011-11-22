<?php

class Model_DbTable_PostsLabels extends Zend_Db_Table_Abstract {
	protected $_name = 'posts_labels';
	
	protected $_referenceMap = array(
		'postsRef' => array(
					'columns' => 'idPost',
					'refTableClass' => 'Model_DbTable_Posts',
					'refColumns' => 'id'
		),
		'labelsRef' => array(
			'columns' => 'idLabel',
			'refTableClass' => 'Model_DbTable_Labels',
			'refColumns' => 'id'
		)
	);
}