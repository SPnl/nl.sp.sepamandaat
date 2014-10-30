<?php

class CRM_Sepamandaat_SepaMandaat {
  
  public static function getMandatesByContact($contact_id) {
    $config = CRM_Sepamandaat_Config_SepaMandaat::singleton();
    $sql = "SELECT * FROM `".$config->getCustomGroupInfo('table_name')."` WHERE `entity_id` = %1";
    $dao = CRM_Core_DAO::executeQuery($sql, array(1=>array($contact_id, 'Integer')));
    $return = array();
    while($dao->fetch()) {
      $return[$dao->id] = array();
      $return[$dao->id]['id'] = $dao->id;
      $return[$dao->id]['contact_id'] = $dao->entity_id;
      foreach($config->getAllCustomFields() as $field) {
        $column = $field['column_name'];
        $return[$dao->id][$field['name']] = $dao->$column;
      }
    }
    return $return;
  }
  
  public static function isExistingMandaat($mandaat_id, $contact_id) {
    $config = CRM_Sepamandaat_Config_SepaMandaat::singleton();
    $mandaat_field = $config->getCustomField('mandaat_nr', 'column_name');
    $sql = "SELECT * FROM `".$config->getCustomGroupInfo('table_name')."` WHERE `entity_id` = %1 AND `".$mandaat_field."` = %2";
    $dao = CRM_Core_DAO::executeQuery($sql, array(
      1=>array($contact_id, 'Integer'), 
      2 => array($mandaat_id, 'String'))
    );
    if ($dao->fetch()) {
      return true;
    }
    return false;
  }
  
  public static function findMandaatIdByMandaatNr($mandaat_nr) {
    $config = CRM_Sepamandaat_Config_SepaMandaat::singleton();
    $mandaat_field = $config->getCustomField('mandaat_nr', 'column_name');
    $sql = "SELECT * FROM `".$config->getCustomGroupInfo('table_name')."` WHERE `".$mandaat_field."` = %1";
    $dao = CRM_Core_DAO::executeQuery($sql, array(
      1 => array($mandaat_nr, 'String'))
    );
    if ($dao->fetch()) {
      return $dao->id;
    }
    return false;
  }
  
  public static function getNewMandaatIdForContact($contact_id) {
    $config = CRM_Sepamandaat_Config_SepaMandaat::singleton();
    $table_name = $config->getCustomGroupInfo('table_name');
        
    $sql = "SELECT COUNT(*) + 1 AS `total` FROM `".$table_name."` WHERE LENGTH(`".$config->getCustomField('mandaat_nr', 'column_name')."`) > 0 AND `entity_id` = %1";
    $dao = CRM_Core_DAO::executeQuery($sql, array(1 => array( $contact_id, 'Integer')));
    $seqNr = 1;
    if ($dao->fetch()) {
      $seqNr = $dao->total;
    }
    
    $mandaat_id = str_pad($contact_id, 8, "0", STR_PAD_LEFT);
    $mandaat_id .= '-';
    $mandaat_id .= str_pad($seqNr, 4, "0", STR_PAD_LEFT);
    
    return $mandaat_id;
  }
  
}

