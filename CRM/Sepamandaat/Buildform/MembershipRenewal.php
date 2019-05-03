<?php

class CRM_Sepamandaat_Buildform_MembershipRenewal extends CRM_Sepamandaat_Buildform_Sepamandaat {

  protected function getName() {
    return 'membershiprenewal';
  }

  protected function getContactIdFromValues($values) {
    $contactId = '';
    if (!empty($this->form->getVar('_contactID'))) {
      $contactId = $this->form->getVar('_contactID');
    }

    //check if contribution is recorded for someone else
    if (isset($values['contribution_contact']) && isset($values['contribution_contact'][1]) && !empty($values['contribution_contact'][1])) {
      $contactId = $values['contribution_contact_select_id'][1];
    } elseif (isset($values['contact_select_id']) && isset($values['contact_select_id'][1])) {
      $contactId = $values['contact_select_id'][1];
    }
    return $contactId;
  }

  protected function getCurrentSepamandaat($contactId) {
    $config = CRM_Sepamandaat_Config_MembershipSepaMandaat::singleton();
    $table = $config->getCustomGroupInfo('table_name');
    $mandaat_id_field = $config->getCustomField('mandaat_id', 'column_name');

    $mid = $this->form->getVar('_id');
    if ($mid) {
      //set default value
      $sql = "SELECT * FROM `" . $table . "` WHERE `entity_id` = %1";
      $dao = CRM_Core_DAO::executeQuery($sql, array(1 => array($mid, 'Integer')));
      if ($dao->fetch()) {
        $mandaat_id = $dao->$mandaat_id_field;
        if (CRM_Sepamandaat_SepaMandaat::isExistingMandaat($mandaat_id, $contactId)) {
          return $mandaat_id;
        }
      }
    }
    return false;;
  }

}
