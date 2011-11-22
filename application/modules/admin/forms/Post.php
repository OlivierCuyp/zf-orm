<?php
class Admin_Form_Post extends Zend_Form {
	
	public function init() {
		$this->setName('formPost')
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
		$element->setLabel('Label')
			->setAttribs(array(
				'class' => 'wideInput'
			))
			->setRequired(true)
			->addFilters(array('StringTrim', 'StripTags'))
			->addValidators(array(
				'NotEmpty'
			));
		$this->addElement($element);
		
		// Labels element
		$labelsMapper = Model_Mapper_Core::deliver('Model_Mapper_Labels');
		$labels = $labelsMapper->fetchAll();
		
		$options = array();
		foreach($labels as $label) {
			$options[$label->id] = $label->text;
		}
		$element = new Zend_Form_Element_MultiCheckBox('idLabels');
		$element->setLabel('Labels')
			->setMultiOptions($options)
			->setRequired(true)
			->addValidators(array(
				'NotEmpty'
			)
		);
		
	}
}

?>