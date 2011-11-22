<?php
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {
	
	protected function _initDbUtf8() {
    	$this->bootstrap('db');
		$db = $this->getResource('db');
		
		$dbConnexion = $db->getConnection(); 
		$dbConnexion->exec('SET NAMES "utf8"');
		
		Zend_Registry::set('dbConnexion', $dbConnexion);
		return $db;
    }
	
	protected function _initAutoload() {
        $moduleLoader = new Zend_Application_Module_Autoloader(array(
                        'namespace' => '',
                        'basePath' => APPLICATION_PATH.'/modules/default'));
        
        return $moduleLoader;
	}
}