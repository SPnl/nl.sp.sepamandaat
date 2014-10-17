<?php

/**
 * Config class for SEPA Mandaat custom group
 * 
 */
class CRM_Sepamandaat_Config_ContributionSepaMandaat {
  
  protected static $_singleton;
  
  protected $customgroup;
  
  protected $fields;
  
  protected function __construct() {
    $this->customgroup = civicrm_api3('CustomGroup', 'getsingle', array('name' => 'Contribution_SEPA_Mandaat'));
    $fields = civicrm_api3('CustomField', 'get', array('custom_group_id' => $this->customgroup['id']));
    foreach($fields['values'] as $field) {
      $this->fields[$field['name']] = $field;
    }
  }
  
  /**
   * @return CRM_Sepamandaat_Config_ContributionSepaMandaat
   */
  
  public static function singleton() {
    if (!self::$_singleton) {
      self::$_singleton = new CRM_Sepamandaat_Config_ContributionSepaMandaat();
    }
    return self::$_singleton;
  }
  
  public function getCustomGroupInfo($key=false) {
    if (!$key) {
      return $this->customgroup;
    } elseif (isset($this->customgroup[$key])) {
      return $this->customgroup[$key];
    }
    return false;
  }
  
  public function getCustomField($field, $key=false) {
    if (!isset($this->fields[$field])) {
      throw new Exception('Unknown field '.$field);
    }
    if (!$key) {
      return $this->fields[$field];
    } elseif (isset($this->fields[$field][$key])) {
      return $this->fields[$field][$key];
    }
    return false;
  }
  
  public function getAllCustomFields() {
    return $this->fields;
  }
  
}

