<?php

class CRM_Sepamandaat_Post_MembershipPayment {
  
  public function __construct() {
    
  }
  
  public function post($op, $object) {
    if ($op == 'create' || $op == 'edit') {
      //remove iban account details from contribution record
      $contribution_id = $object->contribution_id;
      $mandaat_id = $this->getMandaatIdFromMembership($object->membership_id);      
      $this->clearMandaatId($contribution_id);
      if ($mandaat_id) {
        $this->saveMandaatId($contribution_id, $mandaat_id);
      }
    }
  }
  
  public function clearMandaatId($contribution_id) {
    $config = CRM_Sepamandaat_Config_ContributionSepaMandaat::singleton();
    $table = $config->getCustomGroupInfo('table_name');
    
    CRM_Core_DAO::executeQuery("DELETE FROM `" . $table . "` WHERE `entity_id` = %1", array(1 => array($contribution_id, 'Integer')));
  }
  
  public function saveMandaatId($contribution_id, $mandaat_id) {    
    $config = CRM_Sepamandaat_Config_ContributionSepaMandaat::singleton();
    $table = $config->getCustomGroupInfo('table_name');
    $mandaat_id_field = $config->getCustomField('mandaat_id', 'column_name');
    
    $sql = "INSERT INTO `" . $table . "` (`entity_id`, `" . $mandaat_id_field . "`) VALUES (%1, %2);";
    $dao = CRM_Core_DAO::executeQuery($sql, array(
          '1' => array($contribution_id, 'Integer'),
          '2' => array($mandaat_id, 'String'),
    ));
  }
  
  protected function getMandaatIdFromMembership($membership_id) {
    $config = CRM_Sepamandaat_Config_MembershipSepaMandaat::singleton();
    $table = $config->getCustomGroupInfo('table_name');
    $mandaat_id_field = $config->getCustomField('mandaat_id', 'column_name');

    if ($membership_id) {
      //set default value
      $sql = "SELECT * FROM `" . $table . "` WHERE `entity_id` = %1";
      $dao = CRM_Core_DAO::executeQuery($sql, array(1 => array($membership_id, 'Integer')));
      if ($dao->fetch()) {
        return $dao->$mandaat_id_field;
      }
    }
    return false;
  }
  
}

