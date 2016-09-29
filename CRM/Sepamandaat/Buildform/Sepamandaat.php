<?php

abstract class CRM_Sepamandaat_Buildform_Sepamandaat {

  //abstract public function postProcess();
  
  abstract protected function getContactIdFromValues($values);

  abstract protected function getName();
  
  abstract protected function getCurrentSepamandaat($contactId);

  protected $form;

  public function __construct(&$form) {
    $this->form = $form;
  }

  protected function generateOptions($contactId) {
    $options = array();
    $options[] = ts(' -- Select Sepa mandaat --');
    if (strlen($contactId)) {
      //the contact id is already set on this form so set the information static
      $mandaten = CRM_Sepamandaat_SepaMandaat::getMandatesByContact($contactId);

      foreach ($mandaten as $id => $mandaat) {
        $label = $mandaat['mandaat_nr'];
        if (strlen($mandaat['subject'])) {
          $label = $mandaat['subject'] . ' ('.$mandaat['mandaat_nr'].')';
        }
        $options[$mandaat['mandaat_nr']] = $label;
      }
    }
    return $options;
  }
  
  /**
   * Add the UI code to the form
   */
  public function parse() {
    $values = $this->form->exportValues();
    $contactId = $this->getContactIdFromValues($values);

    $options = $this->generateOptions($contactId);
    
    

    $snippet['template'] = 'CRM/Sepamandaat/Buildform/'.ucfirst($this->getName()).'.tpl';
    $snippet['contact_id'] = $contactId;

    $this->form->add('select', 'mandaat_id', ts('Mandaat'), $options);

    $currentMandaat = $this->getCurrentSepamandaat($contactId);

    if ($currentMandaat) {
      $defaults['mandaat_id'] = $currentMandaat;
      $this->form->setDefaults($defaults);
    }

    CRM_Core_Region::instance('page-body')->add($snippet);
    CRM_Core_Resources::singleton()->addScriptFile('nl.sp.sepamandaat', 'js/sepamandaat.js', -1, 'page-header');
    CRM_Core_Resources::singleton()->addScriptFile('nl.sp.sepamandaat', 'js/'.strtolower($this->getName()).'.js', 10, 'page-header');
  }

}
