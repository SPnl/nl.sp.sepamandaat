<?php

class CRM_Sepamandaat_Buildform_Contribution extends CRM_Sepamandaat_Buildform_Sepamandaat {
  
  protected function getName() {
    return 'contribution';
  }
  
  protected function getCurrentSepamandaat($contactId) {
    $config = CRM_Sepamandaat_Config_MembershipSepaMandaat::singleton();
    $table = $config->getCustomGroupInfo('table_name');
    $mandaat_id_field = $config->getCustomField('mandaat_id', 'column_name');
    
    $contribution_id = $this->form->getVar('_id');
    if ($contribution_id) {
      //set default value
      $sql = "SELECT * FROM `" . $table . "` WHERE `entity_id` = %1";
      $dao = CRM_Core_DAO::executeQuery($sql, array(1 => array($contribution_id, 'Integer')));
      if ($dao->fetch()) {
        $mandaat_id = $dao->$mandaat_id_field;
        if (CRM_Sepamandaat_SepaMandaat::isExistingMandaat($mandaat_id, $contactId)) {
          return $mandaat_id;
        }
      }
    }
    return false;;
  }
  
  protected function getContactIdFromValues($values) {
    $contactId = '';
    if ($this->form && !empty($this->form->getVar('_contactID'))) {
      $contactId = $this->form->getVar('_contactID');
    }
    return $contactId;
  }
  
}

