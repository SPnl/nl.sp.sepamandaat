<?php

class CRM_Sepamandaat_OdooSync_Definition extends CRM_Odoosync_Model_ObjectDefinition implements CRM_Odoosync_Model_ObjectDependencyInterface {
  
  /**
   *
   * @var CRM_Sepamandaat_Config_SepaMandaat 
   */
  protected $config;
  
  public function __construct() {
    $this->config = CRM_Sepamandaat_Config_SepaMandaat::singleton();
  }
  
  public function isObjectNameSupported($objectName) {
    if ($objectName == $this->config->getCustomGroupInfo('table_name')) {
      return true;
    }
    return false;
  }
  
  public function getName() {
    return $this->config->getCustomGroupInfo('table_name');
  }
  
  public function getCiviCRMEntityName() {
    return $this->config->getCustomGroupInfo('table_name');
  }

  public function getWeight($action) {
    return -20; //lower than contribution
  }
  
  public function getSynchronisatorClass() {
    return 'CRM_Sepamandaat_OdooSync_Synchronisator';
  }
  
  public function getSyncDependenciesForEntity($entity_id, $data=false) {
    $dep = array();
    try {
      if (is_array($data) && isset($data['contact_id'])) {
         $contact_id = $data['contact_id'];
         $dep[] = new CRM_Odoosync_Model_Dependency('civicrm_contact', $contact_id);
      }
      if (is_array($data) && isset($data['iban_id'])) {
        $iban_config = CRM_Ibanaccounts_Config::singleton();
        $table = $iban_config->getIbanCustomGroupValue('table_name');
        $dep[] = new CRM_Odoosync_Model_Dependency($table, $data['iban_id']);
      }
    } catch (Exception $ex) {
       //do nothing
    }

    return $dep;
  }
  
  public function getCiviCRMEntityDataById($id) {
    $table = $this->config->getCustomGroupInfo('table_name');
    
    $sql = "SELECT * FROM `".$table."` WHERE `id` = %1";
    $dao = CRM_Core_DAO::executeQuery($sql, array(1 => array($id, 'Integer')));
    $data = array();
    if ($dao->fetch()) {
      $data['contact_id'] = $dao->entity_id;
      $data['id'] = $dao->id;
      foreach($this->config->getAllCustomFields() as $field) {
        $column = $field['column_name'];
        $data[$field['name']] = $dao->$column;
      }
      
      if (isset($data['IBAN']) && isset($data['contact_id'])) {
        $data['iban_id'] = CRM_Ibanaccounts_Ibanaccounts::getIdByIBANAndContactId($data['IBAN'], $data['contact_id']);
      }
      
      return $data;
    }
    
    throw new Exception('Could not find SEPA Mandaat data for syncing into Odoo');
  }
  
}

