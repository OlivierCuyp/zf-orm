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
		
		// IdAuthor element
		$options = array();
		$usersMapper = Model_Mapper_Core::deliver('Model_Mapper_Users');
		$users = $usersMapper->fetchAll();
		foreach ($users as $user) {
			$options[$user->id] = $user->nickname;
		}
		
		$element = new Zend_Form_Element_Select('idAuthor');
		$element->setLabel('Author')
			->setMultiOptions($options)
			->setRequired(true);
		$this->addElement($element);
		
		// Text element
		$element = new Zend_Form_Element_Textarea('text');
		$element->setLabel('Text')
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
		
		// Submit element
		$element = new Zend_Form_Element_Submit('submit');
		$element->setLabel('Save')
			->setRequired(false)
			->setIgnore(true);
		$this->addElement($element);
	}
}

?>