<?php
/** Zend_Controller_Action */

class Admin_LabelsController extends Zend_Controller_Action {
	protected $_redirector;
	protected $_mapper;
	
	public function init() {
		$this->_redirector = $this->_helper->getHelper('Redirector');
		$this->_mapper = Model_Mapper_Core::deliver('Model_Mapper_Labels');
	}
	
	public function indexAction() {
		$this->view->labels = $this->_mapper->fetchAll();
	}
	
	public function addAction() {
		$form = $this->_mapper->getForm();
		
		$this->request = $this->getRequest();
		
		$form = $this->_mapper->getForm();
		$form->setAction($this->_request->getRequestUri());
		
		if($this->request->isPost()) {
			$post = $this->getRequest()->getPost();
			unset($post['submit']); // Useless and creates error
			$entity = $this->_mapper->create($post);
			if($this->_mapper->save($entity)) {
				return $this->_redirector->gotoSimple('index');
			}
		}
		
		$this->view->form = $form;
	}
	
	public function updateAction() {
		$id = $this->_request->getUserParam('id');
		
		if($id === null) {
			throw new Zend_Exception('Request param id is required to find correct label.');
		}
		
		$form = $this->_mapper->getForm();
		$form->setAction($this->_request->getRequestUri());
		
		if($this->_request->isPost()) {
			$post = $this->getRequest()->getPost();
			unset($post['submit']); // Useless and creates error
			$entity = $this->_mapper->create($post);
			if($this->_mapper->save($entity)) {
				return $this->_redirector->gotoSimple('index');
			}
		}
		else {
			$this->_mapper->populateForm($this->_mapper->find($id));
		}
		
		$this->view->form = $form;
	}
	
	public function deleteAction() {
		$id = $this->_request->getUserParam('id');
		
		if($id === null) {
			throw new Zend_Exception('Request param id is required to find correct label.');
		}
		
		$this->_mapper->delete($id);
		
		return $this->_redirector->gotoSimple('index');
	}
}