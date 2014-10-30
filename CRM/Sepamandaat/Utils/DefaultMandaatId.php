<?php

class CRM_Sepamandaat_Utils_DefaultMandaatId {
  
  public static function custom($op,$groupID, $entityID, &$params ) {
    if ($op != 'create' && $op != 'edit') {
      return;
    }
    
    //check if the group is the Sepa mandaat group
    $mandaat = CRM_Sepamandaat_Config_SepaMandaat::singleton();
    if ($groupID != $mandaat->getCustomGroupInfo('id')) {
      return;
    }
    
    //check if iban is enabled
    $config = CRM_Sepamandaat_Config::singleton();
    if (!$config->isIbanEnabled()) {
      return;
    }
    
    $mandaat_config = CRM_Sepamandaat_Config_SepaMandaat::singleton();
    foreach($params as $param) {
      if ($param['custom_field_id'] == $mandaat_config->getCustomField('mandaat_nr', 'id') && empty($param['value'])) {
        $mandaat_id = CRM_Sepamandaat_SepaMandaat::getNewMandaatIdForContact($param['entity_id']);
        $param['value'] = $mandaat_id;
        $sql = "UPDATE `".$param['table_name']."` SET `".$param['column_name']."` = %1 WHERE `id` = %2";
        CRM_Core_DAO::executeQuery($sql, array(
          1 => array($param['value'], 'String'),
          2 => array($param['id'], 'Integer'),
        ));
      }
    }
  }
}

