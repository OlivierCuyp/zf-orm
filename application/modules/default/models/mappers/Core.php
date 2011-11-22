<?php
/**
 * This class manages the creation of mappers to ensure unicity of the instances
 * @author Olivier
 *
 */
class Model_Mapper_Core {
	// Stores all the mapper instance to ensure unicity
    private static $_mapperInstances = array();

    /**
     * Checks if a mapper class is already instanciated in the cache
     * @param string $mapperClass
     */
    private static function _hasMapper($mapperClass) {
    	return array_key_exists($mapperClass, self::$_mapperInstances);
    }
    
    /**
     * Gets a mapper instance from the cache
     * @param object $mapperClass
     * @return object
     */
    private static function _getMapper($mapperClass) {
    	return self::$_mapperInstances[$mapperClass];
    }
    
    /**
     * Sets a mapper instance in the cache
     * @param string $mapperClass
     * @param object $mapperInstance
     */
    private static function _setMapper($mapperClass, $mapperInstance) {
    	self::$_mapperInstances[$mapperClass] = $mapperInstance;
    }
    
    /**
     * Delivers either a new mapper or a mapper from the cache if it already exists
     * @param string $mapperClass
     * @param mixed $ param
     * @return Model_Mapper_Abstract
     */
    public static function deliver($mapperClass) {
    	$instance = null;
    	if(self::_hasMapper($mapperClass)) {
    		$instance = self::_getMapper($mapperClass);
    	}
    	else {
    		$instance = new $mapperClass;
    		self::_setMapper($mapperClass, $instance);
    	}
    	
    	return $instance;
    }
}