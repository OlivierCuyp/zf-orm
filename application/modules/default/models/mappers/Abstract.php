<?php
/**
 * Absctract Mapper class that predefine most of the usual access methods 
 * @author Olivier
 *
 */
abstract class Model_Mapper_Abstract {
	// The only instance of the mapper
	protected static $_instance = null;
	// DBTable class name that should be set by the child class
	protected $_tableGatewayClass;
	// Data gateway instance
	protected $_tableGateway;
	// Class name of the entity that should be set by the child class
	protected $_entityClass;
	// Primary key name
	protected $_primary;
	// Gateways used in many to many relation which doesn't have a Mapper
	protected $_relationsTableGateways = array();
	// Validation form class name that should be set by the child class
	protected $_formClass;
	// Validation form
	protected $_form;
	/**
	 * All attributes that have a reference mapper with the corresponding entity and a early behavior flag
	 * The structure is :
	 * 	'attribute_name' => [
	 * 		'mapperClass => 'Mapper_Class_Name',
	 * 		'mapper' => instance,
	 * 		'method' => 'mapper_method_name', // Need to be set only if it find 
	 * 		'field' => 'gateway_field_name',
	 * 		'earlyLoad' => true/false
	 * 	]
	 * @var array
	 */ 
	protected $_referenceMap = array();
	/**
	* All attributes that have a dependent mapper with the corresponding entity and a early behavior flag
	* The structure is :
	* 	'attribute_name' => [
	* 		'mapperClass => 'Mapper_Class_Name',
	* 		'mapper' => instance,
	* 		'method' => 'mapper_method_name',
	* 		'earlyLoad' => true/false,
	* 		// This part is only for Many to Many relations
	*		'relationGatewayClass' => 'Gateway_Class_Name',
	*		'relationGateway' => instance,
	*		'relationKeys' => array(
	*			'keys' => array('entity_attribute_name', 'gateway_field_name'),
	*			'inverseKeys' => array('sub_entity_attribute_name', 'gateway_field_name')
	*		)
	* 	]
	* 
	* @var array
	*/
	protected $_dependenciesMap = array();
	/**
	 * All fields of the validation form that are used to create many to many dependent field
	 * The structure is :
	 * 	'attribute_name' => 'dependent_attribute_name'
	 * @var array
	 */
	protected $_dependentFormFields = array();
	// Caches all the model entity already loaded
	protected $_identityMap = array();
	// All the valid fetch options with their defaults
	/**
	* All attributes that have a special behavior for save.
	* The method that manage the behavior should be protected and will be called with attribute's name, the entity and the filtered data passed by reference as arguments 
	* The structure is :
	* 	'attribute_name' => ['method_name']
	* The method signature will like this:
	* 	method_name($name, $entity, &$filteredData)
	*/
	protected $_preSaveBehaviors = array();
	protected $_postSaveBehaviors = array();
	protected $_fetchOptions = array();
	protected $_fetchParams = array(
		'order' => null,
		'limit' => null,
		'page' => null
	);
	
	/**
	 * Constructor of the mapper (should only be called by the fabric to garanty unicity)
	 */
	public function __construct() {
		if(empty($this->_tableGatewayClass)) {
			throw new Zend_Exception('$_tableGatewayClass should be a valid Zend_DBTable class name.');
		}
		if(empty($this->_entityClass)) {
			throw new Zend_Exception('$_entityClass should be a valid Model class name.');
		}
		$this->_tableGateway = new $this->_tableGatewayClass();
	}
	
	/**
	 * Gets the data gateway
	 */
    protected function _getGateway() {
        return $this->_tableGateway;
    }
	
    /**
     * Checks in the identity map if id exists or not
     * @param mixed $id
     * @param object $entity
     */
    protected function _hasIdentity($id) {
    	return array_key_exists($id, $this->_identityMap);
    }
    
    /**
    * Gets an entity from the identity map
    * @param mixed $id
    * @return object
    */
    protected function _getIdentity($id) {
    	if ($this->_hasIdentity($id)) {
    		return $this->_identityMap[$id];
    	}
    }
    
	/**
	 * Sets an entity in the identity map
	 * @param mixed $id
	 * @param object $entity
	 */
	protected function _setIdentity($id, $entity) {
		if (!$this->_hasIdentity($id)) {
			$this->_identityMap[$id] = $entity;
		}
	}
	
	/**
	 * Unsets an entity in the identity map
	 * @param mixed $id
	 */
	protected function _unsetIdentity($id) {
		if ($this->_hasIdentity($id)) {
			unset($this->_identityMap[$id]);
		}
	}
	 
	/**
	 * Gets the primary key from the table
	 */
	protected function _getPrimary() {
		if(empty($this->_primary)) {
			$primary = $this->_getGateway()->info(Zend_Db_Table_Abstract::PRIMARY);
			$this->_primary = count($primary) == 1 ? $primary[1] : $primary;
		}
		
		return $this->_primary;
	}

	/**
	 * Checks is primary field are set on the entity
	 * @param object $entity
	 * @return boolean
	 */
	protected function _hasPrimary($entity) {
		if(is_array($this->_getPrimary())) {
			foreach($this->_getPrimary() as $primaryPart) {
				if(null === ($entity->{$primaryPart})) {
					$hasPrimary = false;
					break;
				}
			}
		}
		else {
			$hasPrimary = !is_null($entity->{$this->_getPrimary()});
		}
		
		return $hasPrimary;
	}
    
    /**
     * Gets the class name of the validation from
     */
	protected function _getFormClass() {
        if (empty($this->_formClass)) {
        	if(empty($this->_formClass)) {
        		throw new Exception('$_formClass should be a valid Zend_Form class name.');
        	}
            $this->_formClass = str_replace('Model_Mapper', 'Form', get_class($this));
        }
        
        return $this->_formClass;
    }
    
    /**
     * Gets the mapper for the entity attribute's name
     * @param string $name
     */
    protected function _getReferenceMapper($name) {
    	if(!isset($this->_referenceMap[$name]['mapper']) || is_null($this->_referenceMap[$name]['mapper'])) {
    		$mapperClass = $this->_referenceMap[$name]['mapperClass'];
    		$this->_referenceMap[$name]['mapper'] = new $mapperClass;
    	}
    
    	return $this->_referenceMap[$name]['mapper'];
    }
    
    /**
     * Gets the reference field for the entity attribute's name
     * @param string $name
     */
    protected function _getReferenceField($name) {
    	return $this->_referenceMap[$name]['field'];
    }
    
    /**
     * Checks if the field is a referenced field
     * @param string $name
     */
    protected function _isReference($name) {
    	return array_key_exists($name, $this->_referenceMap);
    }
    
    /**
     * Gets a dependencies mapper
     * @param string $name
     */
    protected function _getDependentMapper($name) {
    	
    	if(!isset($this->_dependenciesMap[$name]['mapper']) || is_null($this->_dependenciesMap[$name]['mapper'])) {
    		$mapperClass = $this->_dependenciesMap[$name]['mapperClass'];
    		$this->_dependenciesMap[$name]['mapper'] = Model_Mapper_Core::deliver($mapperClass);
    	}
    
    	return $this->_dependenciesMap[$name]['mapper'];
    }
    
    /**
     * Get the dependent mapper method to use to retrieve corrects entites
     * @param string $name
     */
    protected function _getDependentMethod($name) {
    	if(!isset($this->_dependenciesMap[$name]['method']) || is_null($this->_dependenciesMap[$name]['method'])) {
			$entityBaseName = str_replace('Model_', '', $this->_entityClass);
			$method = 'fetchBy' . $entityBaseName;
    	}
    	 
    	return $this->_dependenciesMap[$name]['method'];
    }
    
    /**
     * Get the dependent options to use with the mapper method
     * @param string $name
     */
    protected function _getDependentMethodOptions($name) {
    	return $this->_dependenciesMap[$name]['methodOptions'];
    }
    
    /**
     * Checks if the field has dependencies
     * @param string $name
     */
    protected function _isDependence($name) {
    	return array_key_exists($name, $this->_dependenciesMap);
    }
    
    /**
     * Check if the field has a relation gateway (only in case of many to many relation)
     * @param string $name
     * @return boolean
     */
    protected function _hasRelationGateway($name) {
    	return isset($this->_dependenciesMap[$name]['relationGatewayClass']);
    }
    
    /**
     * Gets relation gateway
     * @param string $name
     */
    protected function _getRelationGateway($name) {
    	if(!isset($this->_dependenciesMap[$name]['relationGateway']) || is_null($this->_dependenciesMap[$name]['relationGateway'])) {
    		$this->_dependenciesMap[$name]['relationGateway'] = new $this->_dependenciesMap[$name]['relationGatewayClass'];
    	}
    	return $this->_dependenciesMap[$name]['relationGateway'];
    }
    
    /**
     * Gets relation keys
     * @param string $name
     */
    protected function _getRelationKeys($name) {
    	return $this->_dependenciesMap[$name]['relationKeys'];
    }
    
    /**
     * Checks if the field is a special many to many form field
     * @param string $name
     */
    protected function _isDependentFormField($name) {
    	return array_key_exists($name, $this->_dependentFormFields);
    }
    
    /**
    * Checks if the field is a special many to many form field
    * @param string $name
    */
    protected function _getDependentFormField($name) {
    	return $this->_dependentFormFields[$name];
    }
    
    /**
     * Registers a new pre save behavior for a attribute
     * @param string $name
     * @param string $method
     */
    protected function _registerPreSaveBehavior($method) {
    	if (!in_array($method, $this->_preSaveBehaviors)) {
    		$this->_preSaveBehaviors[] = $method;
    	}
    }
    
    /**
    * Registers a new post save behavior for a attribute
    * @param string $name
    * @param string $method
    */
    protected function _registerPostSaveBehavior($method) {
    	if (!in_array($method, $this->_postSaveBehaviors)) {
    		$this->_postSaveBehaviors[] = $method;
    	}
    }
    
    protected function _getSaveBehaviorMethod($name) {
    	return $this->_preSaveBehaviors[$name];
    }
    
    /**
     * Allows to manage wildcard as * instead of % and uses the LIKE instead of the = operator
     * @param string $field
     * @return string
     */
    protected function _wildcardize($field) {
    // TODO: Need to be improve, protection caracter should be found 
		$where = array();
		if (strpos($value, '*') !== false) {
			$where["{$field} LIKE ?"] = str_replace('*', '%', $value);
		} 
		else {
			$where["{$column} = ?"] = $value;
		}
    	
		return $where;
    }
    
    /**
     * Manages all the criterias of the select
     * @param object $select
     * @param array $options
     */
    abstract protected function _manageFetchOptions($select, $options);
    
    /**
     * Manages the order, limit and page parameters of the select
     * @param object $select
     * @param string|array $order
     * @param integer $limit
     * @param integer $page
     */
    protected function _manageFetchParams($select, $params) {
    	if($params['order'] !== null) {
    		$select->order($params['order']);
    	}
    	if($params['limit'] !== null) {
    		if($params['page'] !== null) {
    			$select->limitPage($params['page'], $params['limit']);
    		}
    		else {
    			$select->limit($params['limit']);
    		}
    	}
    }
    
    /**
     * Validate fetchOptions
     * @param array $options
     * @throws Exception
     */
    protected function _validateFetchOptions($options) {
    	foreach(array_keys($options) as $key) {
    		if(!array_key_exists($key, $this->_fetchOptions)) {
    			throw new Zend_Exception('Invalid fetch options : ' . $key);
    		}
    	}
    }
    
    /**
    * Validate fetchParams
    * @param array $params
    * @throws Exception
    */
    protected function _validateFetchParams($params) {
    	foreach(array_keys($params) as $key) {
    		if(!array_key_exists($key, $this->_fetchParams)) {
    			throw new Zend_Exception('Invalid fetch params : ' . $key);
    		}
    	}
    }
    
    /**
     * Convert a row in a entity
     * @param Zend_Db_Table_Row $row
     * @return Model_Abstract
     */
    protected function _rowToEntity($row) {
    	$id = $row->{$this->_getPrimary()};
    	
    	if($this->_hasIdentity($id)) {
    		$entity = $this->_getIdentity($id);
    	}
    	else {
    		$entity = $this->create();
    	
			foreach($entity->getAttributes() as $name) {
				if($this->_isReference($name)) {
					$refId = $row->{$this->_getReferenceField($name)};
	    			if($this->_referenceMap[$name]['earlyLoad']) {
	    				$entity->{$name} = $this->getReferenceEntity($name, $refId);
	    			}
	    			// Register the id on the entity for lazy load
	    			else {
	    				$entity->addLazyReference($name, $refId);
	    			}
	    		}
	    		elseif($this->_isDependence($name)) {
	    			if($this->_dependenciesMap[$name]['earlyLoad']) {
	    				$entity->{$name} = $this->getDependentEntities($name, $id);
	    			}
	    			else {
	    				$entity->addLazyDependencies($name);
	    			}
	    		}
	    		else {
	    			$entity->{$name} = $row->{$name};
	    		}
	    	}
	    	$this->_setIdentity($id, $entity);
    	}
    	
    	return $entity;
    }
    
    /**
     * Convert a rowset in an array of entities
     * @param Zend_Db_Table_Rowset $rowset
     * @return array
     */
	protected function _rowsetToEntites($rowset) {
		$entities = array();
		foreach ($rowset as $row) {
			$entities[] = $this->_rowToEntity($row);
		}
		return $entities;
	}
    
	/**
	 * Used by entity to lazy load referenced entities of an entity
	 * @param Model_Abstract $entity
	 * @param string $name
	 */
	public function getReferenceEntity($name, $referenceId) {
		$method = 'find';
		if(isset($this->_referenceMap[$name]['method']) && !empty($this->_referenceMap[$name]['method'])) {
			$method = $this->_referenceMap[$name]['method'];
		}
		return $this->_getReferenceMapper($name)->{$method}($referenceId);
	}
	
	/**
	 * Gets the dependent entities from the dependent mapper
	 * @param string $name
	 * @param mixed $id
	 * @param array $options
	 */	
    public function getDependentEntities($name, $id, $options = array()) {
    	// TODO: Need to implement checks and exception
    	return $this->_getDependentMapper($name)->{$this->_getDependentMethod($name)}($id, $options);
    }
	
    /**
    * Returns the validation form
    */
    public function getForm() {
        if ($this->_form === null) {
            $this->_form = new $this->_formClass;
        }
        return $this->_form;
    }
    
    /**
    * Returns last validation errors
    */
	public function getErrors() {
    	$this->getForm()->getErrors();
    }
    
    /**
     * Returns last validation messages
     */
    public function getMessages() {
    	$this->getForm()->getMessages();
    }
	
    /**
     * Check that an attribute has the correct type
     * @param string $name
     * @param string $value
     */
    protected function _checkAttributesValue(&$data, $name) {
    	if($this->_isReference($name) && is_array($data[$name])) {
    		$data[$name] = $this->_getReferenceMapper($name)->create($data[$name]);
    	}
    	elseif($this->_isDependence($name)) {
    		foreach($data[$name] as $key => $value) {
    			if(is_array($value)) {
    				$data[$name][$key] = $this->_getDependentMapper($name)->create($value);
    			}
    		}
    	}
    	elseif($this->_isDependentFormField($name)) {
    		$dependentAttribute = $this->_getDependentFormField($name);
    		// TODO: Find a better way to check, count isn't the best check, it doesn't avoid double work ...
    		if(count($data[$dependentAttribute]) < count($data[$name])) {
	    		foreach($data[$name] as $id) {
	    			$data[$dependentAttribute][] = $this->_getDependentMapper($dependentAttribute)->find($id);
	    		}
    		}
    		unset($data[$name]);
    	}
    }
    
    /**
     * Creates a new entity not yet persistant
     * @param array $data
     */
    public function create($data = array()) { 
    	foreach(array_keys($data) as $name) {
    		$this->_checkAttributesValue($data, $name);
    	}
    	
    	return $entity = new $this->_entityClass($this, $this->_getPrimary(), $data);
    }
    
    /**
     * Populate the validation form from an id
     * @param Model_Abstract $entity
     */
    public function populateForm($entity) {
    	$data = $entity->toArray();
    	
    	// TODO: Check if it really works
    	foreach($entity->getAttributes() as $attribute) {
    		if(!$this->_isReference($attribute) && !$this->_isDependence($attribute)) {
    			$data[$attribute] = $entity->{$attribute};
    		}
    	}
    	$form = $this->getForm();
    	$form->populate($data);
    }
    
	/**
	 * Save an entity
	 * @param Model_Abstract|array $entity
	 */
	public function save($entity) {
		$data = $entity->toArray();
		
		$form = $this->getForm();
		if ($isValid = $form->isValid($data)) {
			$gateway = $this->_getGateway();
			$filteredData = $form->getValues();
			$primary = $this->_getPrimary();
			
			// Pre-save hook
			foreach ($this->_preSaveBehaviors as $preSaveMethod) {
				$this->{$preSaveMethod}($entity, $filteredData);
			}
			// Insert case
			if(empty($filteredData[$primary])) {
				unset($filteredData[$primary]);
				$entity->{$primary} = $gateway->insert($filteredData);
			}
			// Update case
			else {
				$where = $gateway->getAdapter()->quoteInto($primary . ' = ?', $filteredData[$primary]);
				$gateway->update($filteredData, $where);
			}
			// Manage manty to many relations
			foreach ($filteredData as $name => $value) {
				if($this->_isDependence($name) && $this->_hasRelationGateway($name)) {
					$relationGateway = $this->_getRelationGateway($name);
					$relationsKeys = $this->_getRelationKeys($name);
					$id = $entity->{$relationsKeys['keys'][0]};
					
					// First delete old relations
					$where = $relationGateway->getAdapter()->quoteInto($relationsKeys['keys'][1] . ' = '. $id);
					$relationGateway->delete($where);
					
					// Then create the new relations
					foreach($value as $subValue) {
						$data = array();
						$subId = $subValue[$relationsKeys['inverseKeys'][0]];
						
						$data[$relationsKeys['keys'][1]] = $id;
						$data[$relationsKeys['inverseKeys'][1]] = $subId;
						
						$relationGateway->insert($data);
					}
				}
			}
			// Post-save hook
			foreach ($this->_preSaveBehaviors as $postSaveMethod) {
				$this->{$postSaveMethod}($entity, $filteredData);
			}
		}
		
		return $isValid;
	}
	
	/**
	* Delete an entity from itself or from its id
	* @param mixed $entity
	*/
	public function delete($entity) {
		if($entity instanceof Model_Abstract) {
			$id = $entity->getIdentityValue();
		}
		else {
			$id = $entity;
		}
		// Need to unset the entity in the identity map if it exists
		$this->_unsetIdentity($id);
		 
		$rowset = $this->_getGateway()->find($id);
		if($rowset->count() > 0) {
			$row = $rowset->current();
			$result = $row->delete();
		}
	}
	
	/**
	 * Finds the entity corresponding to the id
	 * @param mixed $id
	 */
	public function find($id) {
		// Tries first the identity cache
		if($this->_hasIdentity($id)) {
			return $this->_getIdentity($id);
		}
		// Else get entity from table
		$rowset = $this->_getGateway()->find($id);
		if(count($rowset)) {
			return $this->_rowToEntity($rowset->current());
		}
	}
	
	/**
	 * Checks if the id exists
	 * @param mixed $id
	 */
	public function exists($id) {
		//Checks first in the identity cache
		if($this->_hasIdentity($id)) {
			return true;
		}
		// Else checks in from table
		$rowset = $this->_getGateway()->find($id);
		return count($rowset) > 0;
	}
    
	/**
	* Count the number of entities matching the options
	* @param array $options
	*/
	public function count($options = array(), $debug = false) {
		$options += $this->_fetchOptions;
	
		$this->_validateFetchOptions($options);
		 
		$gateway = $this->_getGateway();
		$select = $gateway->select();
		$select->from($gateway->info(Zend_Db_Table_Abstract::NAME), array('count' => 'COUNT(*)'));
		$this->_manageFetchOptions($select, $options);
		
		if($debug) {
			Zend_Debug::dump($select); die();
		}
		
		$rowset = $gateway->fetchAll($select);
		 
		return $rowset->current()->count;
	}	
	
	/**
	 * Fetch several entities accroding to the fetch options
	 * @param array $options
	 */
	public function fetchAll($options = array(), $params = array(), $debug = false) {
    	$options += $this->_fetchOptions;
    	$params += $this->_fetchParams;
    	
    	$this->_validateFetchOptions($options);
    	$this->_validateFetchParams($params);
    	
    	$gateway = $this->_getGateway();
		
    	$select = $gateway->select();
    	$select->from($gateway->info(Zend_Db_Table_Abstract::NAME));
    	$this->_manageFetchOptions($select, $options);
    	$this->_manageFetchParams($select, $params);
    	
    	if($debug) {
			Zend_Debug::dump($select->assemble()); exit();
    	}
		$rowset = $gateway->fetchAll($select);
		
		return $this->_rowsetToEntites($rowset);
    }
}