<?php

require_once 'sepamandaat.civix.php';

/**
 * Implementation of hook_civicrm_post
 * 
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_post
 * @param type $op
 * @param type $objectName
 * @param type $objectId
 * @param type $objectRef
 */
function sepamandaat_civicrm_post( $op, $objectName, $objectId, &$objectRef ) {
  if ($objectName == 'MembershipPayment') {
    $membership_payment = new CRM_Sepamandaat_Post_MembershipPayment();
    $membership_payment->post($op, $objectRef);
  }
}

function sepamandaat_civicrm_pre( $op, $objectName, $objectId, &$params ) {
  if ($objectName == 'Individual' || $objectName == 'Organization' || $objectName == 'Household') {
    CRM_Sepamandaat_Utils_DefaultMandaatId::pre($op, $objectName, $objectId, $params);
  }
}

/**
 * 
 * Implementation of hook_civicrm_buildForm
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_buildForm
 */
function sepamandaat_civicrm_buildForm($formName, &$form) {
 if ($formName == 'CRM_Contact_Form_CustomData') {
   if (CRM_Sepamandaat_Buildform_DefaultMandaatId::isValidForm($form)) {
     $defaultMandaatId = new CRM_Sepamandaat_Buildform_DefaultMandaatId($form);
     $defaultMandaatId->parse();
   }
 }
 if ($formName == 'CRM_Contribute_Form_Contribution') {
   //add template 
   $contribution = new CRM_Sepamandaat_Buildform_Contribution($form);
   $contribution->parse();
 }
 if ($formName == 'CRM_Member_Form_Membership') {
   //add template 
   $membership = new CRM_Sepamandaat_Buildform_Membership($form);
   $membership->parse();
 }
 if ($formName == 'CRM_Member_Form_MembershipRenewal') {
   $membership = new CRM_Sepamandaat_Buildform_MembershipRenewal($form);
   $membership->parse();
 }
}

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
    
    foreach($fields as $key => $value) {
      if (strpos($key, "custom_".$config->getCustomField('IBAN', 'id'))===0) {
        $iban = new IBAN($value);
        if (!$iban->Verify($value)) {
          $errors[$key] = ts("'".$value."' is not a valid IBAN");
        } else {
          $data = &$form->controller->container();
          $data['values']['CustomData'][$key] = $iban->MachineFormat();
        }
      }
    }
    
    CRM_Sepamandaat_Utils_DefaultMandaatId::validateForm($formName, $fields, $files, $form, $errors);
  }
  if ($formName == 'CRM_Contribute_Form_Contribution') {
    // Do validation when payment instrument direct debit is selected and no
    // mandate is selected.
    $direct_debit_payment_instrument_id = civicrm_api3('OptionValue', 'getvalue' , array(
      'option_group_id' => 'payment_instrument',
      'name' => 'sp_automatischincasse',
      'return' => 'value',
    ));
    if ($fields['payment_instrument_id'] == $direct_debit_payment_instrument_id) {
      $contribution_config = CRM_Sepamandaat_Config_ContributionSepaMandaat::singleton();
      $custom_field_id = $contribution_config->getCustomField('mandaat_id', 'id');
      foreach($fields as $field => $value) {
        if (stripos($field, 'custom_'.$custom_field_id)===0 && empty($value)) {
          $errors[$field] = ts('Select a mandate  or a different payment method');
          $errors['mandaat_id'] = ts('Select a mandate  or a different payment method');
          $errors['payment_instrument_id'] = ts('Select a mandate or a different payment method');
        }
      }
    }
  }
  if ($formName == 'CRM_Member_Form_Membership') {
    $direct_debit_payment_instrument_id = civicrm_api3('OptionValue', 'getvalue' , array(
      'option_group_id' => 'payment_instrument',
      'name' => 'sp_automatischincasse',
      'return' => 'value',
    ));
    if (!empty($fields['record_contribution']) && $fields['payment_instrument_id'] == $direct_debit_payment_instrument_id) {
      $membership_config = CRM_Sepamandaat_Config_MembershipSepaMandaat::singleton();
      $custom_field_id = $membership_config->getCustomField('mandaat_id', 'id');
      foreach($fields as $field => $value) {
        if (stripos($field, 'custom_'.$custom_field_id)===0 && empty($value)) {
          $errors[$field] = ts('Select a mandate  or a different payment method');
          $errors['mandaat_id'] = ts('Select a mandate  or a different payment method');
          $errors['payment_instrument_id'] = ts('Select a mandate or a different payment method');
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
  //add default Mandaat ID
  CRM_Sepamandaat_Utils_DefaultMandaatId::custom($op, $groupID, $entityID, $params);
  
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
function sepamandaat_civicrm_iban_usages($iban, $contactId = false) {
  $config = CRM_Sepamandaat_Config_SepaMandaat::singleton();
  $table = $config->getCustomGroupInfo('table_name');
  $iban_field = $config->getCustomField('IBAN', 'column_name');
  $number_field = $config->getCustomField('mandaat_nr', 'column_name');
  
  $sql = "SELECT `i`.`id` AS `id`, `i`.`".$number_field."` AS mandaat_nr FROM `".$table."` `i` WHERE `i`.`".$iban_field."` = %1 AND `i`.`entity_id` = %2";
  $dao = CRM_Core_DAO::executeQuery($sql, array(
    '1' => array($iban, 'String'),
    '2' => array($contactId, 'Integer'),
  ));
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

function sepamandaat_civicrm_odoo_object_definition_dependency(&$deps, CRM_Odoosync_Model_ObjectDefinition $def, $entity_id, $action, $data=false) {
  if ($def instanceof CRM_OdooContributionSync_ContributionDefinition) {
    if (is_array($data) && isset($data['contact_id'])) {
      $contact_id = $data['contact_id'];
    } else {
      try {
        $contact_id = civicrm_api3('Contribution', 'getvalue', array('return' => 'contact_id', 'id' => $entity_id));
      } catch (Exception $e) {
        return;
      }
    }

    _sepamandaat_get_odoo_dependencies($deps, $entity_id);
  }
}

/**
 * Set dependencies for a contribution object for syncing with Odoo
 *
 * @param $deps
 * @param $contribution_id
 */
function _sepamandaat_get_odoo_dependencies(&$deps, $contribution_id, $offset=-1, $queueForUpdate=false) {
  $contribution_config = CRM_Sepamandaat_Config_ContributionSepaMandaat::singleton();
  $mandaat_config = CRM_Sepamandaat_Config_SepaMandaat::singleton();
  $sql = "SELECT `mandaat_id` AS `mandaat_id` FROM `".$contribution_config->getCustomGroupInfo('table_name')."` WHERE `entity_id` = %1";
  $dao = CRM_Core_DAO::executeQuery($sql, array(1 => array($contribution_id, 'Integer')));
  if ($dao->fetch() && $dao->mandaat_id) {
    $mandaat_id = CRM_Core_DAO::singleValueQuery("SELECT id FROM `".$mandaat_config->getCustomGroupInfo('table_name')."` WHERE `mandaat_nr` = %1", array(1 => array($dao->mandaat_id, 'String')));
    if ($mandaat_id) {
      $deps[] = new CRM_Odoosync_Model_Dependency($mandaat_config->getCustomGroupInfo('table_name'), $mandaat_id, $offset, $queueForUpdate);
    }
  }
}

function sepamandaat_civicrm_odoo_alter_parameters(&$parameters, $resource, $entity, $entity_id, $action) {
  if ($entity == 'civicrm_contribution') {
    //add mandaat id to parameter list
    $mandaat_odoo_id = sepamandaat_get_odoo_id_for_contribution_id($entity_id);
    if ($mandaat_odoo_id) {
      $parameters['sdd_mandate_id'] = new xmlrpcval($mandaat_odoo_id, 'int');
    }
  }
}

function sepamandaat_get_odoo_id_for_contribution_id($contribution_id) {
  $mandaat_config = CRM_Sepamandaat_Config_SepaMandaat::singleton();
  $contribution_config = CRM_Sepamandaat_Config_ContributionSepaMandaat::singleton();
  $sql = "SELECT `".$contribution_config->getCustomField('mandaat_id', 'column_name')."` AS `mandaat_id` FROM `".$contribution_config->getCustomGroupInfo('table_name')."` WHERE `entity_id` = %1";
  $dao = CRM_Core_DAO::executeQuery($sql, array(1 => array($contribution_id, 'Integer')));
  if ($dao->fetch() && $dao->mandaat_id) {
    $mandaat_id = CRM_Sepamandaat_SepaMandaat::findMandaatIdByMandaatNr($dao->mandaat_id);
    if ($mandaat_id) {
      $odoo_id = CRM_Odoosync_Model_OdooEntity::findOdooIdByEntityAndEntityId($mandaat_config->getCustomGroupInfo('table_name'), $mandaat_id);
      if ($odoo_id > 0) {
        return $odoo_id;
      }
    }
  }
  return false;
}

/**
 * Implementatio of hook__civicrm_tabs
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_tabs
 */
function sepamandaat_civicrm_tabs(&$tabs, $contactID) {
  if (CRM_Core_Permission::check('access CiviContribute')) {
    return;
  }

  $config = CRM_Sepamandaat_Config_SepaMandaat::singleton();
  $tab_id = 'custom_'.$config->getCustomGroupInfo('id');
  foreach($tabs as $key => $tab) {
    if ($tab['id'] == $tab_id) {
      unset($tabs[$key]);
    }
  }
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
