<?php
/** Zend_Controller_Action */

class IndexController extends Zend_Controller_Action {
	
	public function indexAction() {
		$postsMapper = Model_Mapper_Core::deliver('Model_Mapper_Posts');
		$this->view->posts = $postsMapper->fetchAll();
	}
	
	public function userAction() {
		$id = $this->_request->getUserParam('id');
		
		if($id === null) {
			throw new Zend_Exception('Request param id is required to find correct user.');
		}
		
		$usersMapper = Model_Mapper_Core::deliver('Model_Mapper_Users');
		$postsMapper = Model_Mapper_Core::deliver('Model_Mapper_Posts');
		
		$this->view->user = $usersMapper->find($id);
		$this->view->postCount = $postsMapper->countByAuthor($id);
	}
}