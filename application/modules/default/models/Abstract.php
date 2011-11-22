<?php
abstract class Model_Abstract {
	// Mapper instance
	protected $_mapper = null;
	// Contains entity attribute's name and id association for lazy loading
	protected $_lazyReferences = array();
	// Contains entity attribute's name for dependencies lazy loading
	protected $_lazyDependencies = array();
	//Contains the name(s) of the idenfiant's attribute(s)
	protected  $_identity;
	// Data fields
    protected $_data = array();
	
    /**
     * Creates the object and populates it with the data parameter
     * @param array $data
     */
	public function __construct(Model_Mapper_Abstract $mapper, $identity, array $data = array()) {
		$this->_mapper = $mapper;
		$this->_identity = $identity;
        $this->populate($data);
    }
    
    
    // Sets a entity attribute's name, the corresponding reference id
    public function addLazyReference($name, $refId) {
    	if(!$this->isLazyReference($name)) {
    		$this->_lazyReferences[$name] = $refId;
    	}
    }
    
    // Removes a lazy reference
    protected function _removeLazyReference($name) {
    	unset($this->_lazyReferences[$name]);
    }
    
    // Check if the attribute's name is a reference
    public function isLazyReference($name) {
    	return array_key_exists($name, $this->_lazyReferences);
    }
    
    // Check if the attribute's name is a reference
    protected function _getLazyReferenceId($name) {
    	return $this->_lazyReferences[$name];
    }
    
    // Sets a entity attribute's to be lazy loaded 
    public function addLazyDependencies($name) {
    	if(!$this->isLazyDependencies($name)) {
    		$this->_lazyDependencies[] = $name;
    	}
    }
    
    // Removes a lazy dependencies
    protected function _removeLazyDependencies($name) {
    	$index = array_search($name, $this->_lazyDependencies);
    	if($index !== false) {
    		unset($this->_lazyReferences[$index]);
    	}
    }
    
    // Check if the attribute's name is a reference
    public function isLazyDependencies($name) {
    	return in_array($name, $this->_lazyDependencies);
    }
    
    /**
     * Sets any data field of the model
     * @param string $name
     * @param string $value
     * @throws Exception
     */
	public function __set($name, $value) {
	 	if (!array_key_exists($name, $this->_data)) {
            throw new Exception('Invalid set, property "' . $name . '" doesn\'t exists');
        }
        $method = 'set' . ucfirst($name);
        if (method_exists($this, $method)) {
        	$this->$method($value);
        }
        else {
        	$this->_data[$name] = $value;
        }
    }

    /**
     * Gets any data field of the model
     * @param string $name
     * @return mixed
     */
    public function __get($name) {
		$method = 'get' . ucfirst($name);
		if (method_exists($this, $method)) {
			return $this->$method();
        }
        elseif (!array_key_exists($name, $this->_data)) {
        	throw new Exception('Invalid get, property "' . $name . '" doesn\'t exists');
        }
		elseif($this->isLazyReference($name)) {
			$this->_data[$name] = $this->_mapper->getReferenceEntity($name, $this->_getLazyReferenceId($name));
			$this->_removeLazyReference($name);
		}
		elseif($this->isLazyDependencies($name)) {
			$this->_data[$name] = $this->_mapper->getDependentEntities($name,  $this->getIdentityValue());
			$this->_removeLazyDependencies($name);
		}
		
		return $this->_data[$name];
    }
    
    /**
     * Checks if a data field exists
     * @param string $name
     * @return boolean
     */
 	public function __isset($name) {
        return isset($this->_data[$name]);
    }

    /**
     * Unsets a data field
     * @param string $name
     */
    public function __unset($name) {
        if (isset($this->$name)) {
            $this->_data[$name] = null;
        }
    }
    
    public function getIdentityValue() {
    	return $this->_data[$this->_identity];
    }
    
    /**
     * Populates all the data fields of the model through the setter
     * @param $data
     * @return Model
     */
	public function populate($data) {
		foreach ($data as $name => $value) {
			$this->{$name} = $value;
        }
        return $this;
    }
    
    /**
     * Gets the list of the class attributes
     * @return array 
     */
    public function getAttributes() {
    	return array_keys($this->_data);
    }
    
    /**
     * Used by toArray method to convert recursively a field to an array
     * @param mixed
     * @return mixed
     */
    protected function _toArrayField($field) {
    	$arrayField = null;
    	if (is_array($field)) {
    		$arrayField = array();
    		foreach($field as $subKey => $subField) {
    			$arrayField[$subKey] = $this->_toArrayField($subField);
    		}
    	}
    	elseif (is_object($field) && method_exists($field, 'toArray')) {
    		$arrayField = $field->toArray();  
    	}
    	else {
    		$arrayField = $field;
    	}
    	return $arrayField;
    }    
    
    /**
     * Converts the object into an array
     * @return array 
     */
    public function toArray() {
    	$data = array();
    	
    	foreach($this->getAttributes() as $name) {
    		$data[$name] = $this->_toArrayField($this->{$name});
    	}
    	return $data;
    }
}