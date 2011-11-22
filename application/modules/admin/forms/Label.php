<?php
class Admin_Form_Label extends Zend_Form {
	
	public function init() {
		$this->setName('formLabel')
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
		
		// Text element
		$element = new Zend_Form_Element_Text('text');
		$element->setLabel('Text')
			->setAttribs(array(
				'class' => 'wideInput'
			))
			->setRequired(true)
			->addFilters(array('StringTrim'))
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