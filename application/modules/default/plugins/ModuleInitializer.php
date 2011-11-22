<?php
/**
 * Starts template system only for non-Ajax requests
 */

class Plugin_ModuleInitializer extends Zend_Controller_Plugin_Abstract {
	
	public function routeShutdown(Zend_Controller_Request_Abstract $request) {
		$module = $request->module;
		$controller = $request->controller;
		
		$viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
		
		$view = $viewRenderer->view;
		
		// Enable layout on if its not a ajax request
		if(!$request->isXmlHttpRequest()) {
			Zend_Layout::startMvc();
			$layout = Zend_Layout::getMvcInstance();
			$layout->setLayoutPath(APPLICATION_PATH.'/modules/'.$module.'/layouts')->setLayout($module);
		}
	}
}