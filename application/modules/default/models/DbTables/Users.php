<?php

class Model_DbTableUsers extends Zend_Db_Table_Abstract {
	protected $_name = 'users';
	
	// Even if is isn't really necessary since cascade update & delete is define in the database itself
	protected $_dependentTables = array('Model_DbTable_Posts');
}