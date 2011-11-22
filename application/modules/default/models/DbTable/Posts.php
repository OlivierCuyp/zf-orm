<?php

class Model_DbTable_Posts extends Zend_Db_Table_Abstract {
	protected $_name = 'posts';
	
	protected $_referenceMap = array(
		'authorRef' => array(
			'columns' => 'idUser',
			'refTableClass' => 'Model_DbTable_Users',
			'refColumns' => 'id'
		)
	);
}