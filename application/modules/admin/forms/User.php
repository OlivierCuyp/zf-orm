<?php
class Admin_Form_User extends Zend_Form {
	
	public function init() {
		$this->setName('formUser')
			->setMethod('post');
			
		// Id element
		$element = new Zend_Form_Element_Hidden('id');
		$element->setRequired(false)
			->addFilters(array('StringTrim'))
			->addValidators(array(
				'Int'
			))
			->removeDecorator('Label');
		$this->addElement($element);
		
		// Email element
		$element = new Zend_Form_Element_Text('email');
		$element->setLabel('Email')
			->setAttribs(array(
				'class' => 'mediumInput'
			))
			->setRequired(true)
			->addFilters(array('StringTrim'))
			->addValidators(array(
				'EmailAddress'
			));
		$this->addElement($element);
		
		// Nickname element
		$element = new Zend_Form_Element_Text('nickname');
		$element->setLabel('Nickname')
			->setRequired(false)
			->addFilters(array(
				'StripTags',
				'StringTrim'
			))
			->addValidators(array(
				'NotEmpty',
				array('StringLength', array('min' => 2, true, 'max' => 255))
			));
		$this->addElement($element);
		
		// Submit element
		$element = new Zend_Form_Element_Submit('submit');
		$element->setLabel('Save')
			->setRequired(false)
			->setIgnore(true);
		$this->addElement($element);
	}
}

?>