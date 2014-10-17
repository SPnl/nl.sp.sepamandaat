<?php

require_once 'sepamandaat.civix.php';

/**
 * Validate the entered IBAN account number
 * 
 * 
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_validateForm
 * @param type $formName
 * @param type $fields
 * @param type $files
 * @param type $form
 * @param type $errors
 */
function sepamandaat_civicrm_validateForm( $formName, &$fields, &$files, &$form, &$errors ) {
  if ($formName == 'CRM_Contact_Form_CustomData') {
    $config = CRM_Sepamandaat_Config_SepaMandaat::singleton();
    
    $groupId = $form->getVar('_groupID');
    if ($groupId != $config->getCustomGroupInfo('id')) {
      return;
    }
    
    $iban = new IBAN();
    foreach($fields as $key => $value) {
      if (strpos($key, "custom_".$config->getCustomField('IBAN', 'id'))===0) {
        if (!$iban->Verify($value)) {
          $errors[$key] = ts("'".$value."' is not a valid IBAN");
        }
      }
    }
  }
}

/** 
 * Implementation of hook_civicrm_custom
 * 
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_custom
 */
function sepamandaat_civicrm_custom($op,$groupID, $entityID, &$params ) {
  //add iban to iban list of contact
  CRM_Sepamandaat_Utils_AddToIbanList::custom($op, $groupID, $entityID, $params);
    
  //add to odoo sync queue
  CRM_Sepamandaat_Utils_AddToOdooSyncQueue::custom($op, $groupID, $entityID, $params);
}

/**
 * Check if an iban is in use by a membership
 * 
 * @param type $iban
 */
function sepamandaat_civicrm_iban_usages($iban) {
  $config = CRM_Sepamandaat_Config_SepaMandaat::singleton();
  $table = $config->getCustomGroupInfo('table_name');
  $iban_field = $config->getCustomField('IBAN', 'column_name');
  $number_field = $config->getCustomField('mandaat_nr', 'column_name');
  
  $sql = "SELECT `i`.`id` AS `id`, `i`.`".$number_field."` AS mandaat_nr FROM `".$table."` `i` WHERE `i`.`".$iban_field."` = %1";
  $dao = CRM_Core_DAO::executeQuery($sql, array('1' => array($iban, 'String')));
  $return = array();
  while($dao->fetch()) {
    $return['civicrm_sepa_mandaat'][$dao->id] = ts("IBAN Account is used in Mandaat  '%1'", array(1 => $dao->mandaat_nr));
  }
  return $return;
}

/**
 * Implementation of hook_civicrm_odoo_object_definition
 * 
 */
function sepamandaat_civicrm_odoo_object_definition(&$list) {  
  $config = CRM_Sepamandaat_Config_SepaMandaat::singleton();
  $table_name = $config->getCustomGroupInfo('table_name');
  $list[$table_name] = new CRM_Sepamandaat_OdooSync_Definition();
}

/**
 * Implementation of hook_civicrm_config
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function sepamandaat_civicrm_config(&$config) {
  _sepamandaat_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function sepamandaat_civicrm_xmlMenu(&$files) {
  _sepamandaat_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function sepamandaat_civicrm_install() {
  return _sepamandaat_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function sepamandaat_civicrm_uninstall() {
  return _sepamandaat_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function sepamandaat_civicrm_enable() {
  return _sepamandaat_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function sepamandaat_civicrm_disable() {
  return _sepamandaat_civix_civicrm_disable();
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function sepamandaat_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _sepamandaat_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function sepamandaat_civicrm_managed(&$entities) {
  return _sepamandaat_civix_civicrm_managed($entities);
}

/**
 * Implementation of hook_civicrm_caseTypes
 *
 * Generate a list of case-types
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function sepamandaat_civicrm_caseTypes(&$caseTypes) {
  _sepamandaat_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implementation of hook_civicrm_alterSettingsFolders
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function sepamandaat_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _sepamandaat_civix_civicrm_alterSettingsFolders($metaDataFolders);
}
