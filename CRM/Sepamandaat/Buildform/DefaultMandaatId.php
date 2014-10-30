<?php

class CRM_Sepamandaat_Buildform_DefaultMandaatId {
  
  protected $form;
  
  public function __construct(&$form) {
    $this->form = $form;
  }
  
  public function parse() {
    if (!self::isValidForm($this->form)) {
      return;
    }
    
    $config = CRM_Sepamandaat_Config_SepaMandaat::singleton();
    $cid = CRM_Utils_Request::retrieve('cid', 'Positive', $this->form, TRUE);    
    if ($cid) {
      $mandaat_id = CRM_Sepamandaat_SepaMandaat::getNewMandaatIdForContact($cid);    
      $field = 'custom_'.$config->getCustomField('mandaat_nr', 'id').'_-1';
      $defaults[$field] = $mandaat_id;
      $this->form->setDefaults($defaults);
    }
  }
  
  public static function isValidForm($form) {
    $config = CRM_Sepamandaat_Config_SepaMandaat::singleton();
    if ($config->getCustomGroupInfo('id') == $form->_groupID) {
      return true;
    }
    return false;
  }
  
}

