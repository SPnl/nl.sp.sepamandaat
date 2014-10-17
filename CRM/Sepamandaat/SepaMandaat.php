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
    $sql = "SELECT * FROM `".$config->getCustomGroupInfo('table_name')."` WHERE `entity_id` = %1 AND `id` = %2";
    $dao = CRM_Core_DAO::executeQuery($sql, array(
      1=>array($contact_id, 'Integer'), 
      2 => array($mandaat_id, 'Integer'))
    );
    if ($dao->fetch()) {
      return true;
    }
    return false;
  }
  
}

