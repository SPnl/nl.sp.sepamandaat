<?php

/**
 * Delegate of hook_civicrm_custom
 * 
 * This class will add Iban to the contact IBAN List
 * 
 */
class CRM_Sepamandaat_Utils_AddToIbanList {
  
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
    
    //ok, requirements met
    //add iban to the iban list
    $values = array();
    foreach($params as $param) {
      $values[$param['custom_field_id']] = $param['value'];
    }
    $iban = $values[$mandaat->getCustomField('IBAN','id')];
    $bic = $values[$mandaat->getCustomField('BIC','id')];
    $tnv = $values[$mandaat->getCustomField('tnv','id')];
    $contactId = $entityID;
    
    if (!empty($iban)) {
      CRM_Ibanaccounts_Ibanaccounts::saveIBANForContact($iban, $bic, $tnv, $contactId);
    }
  }
}
