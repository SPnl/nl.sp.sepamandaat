<?php

/**
 * Config class for SEPA Mandaat custom group
 * 
 */
class CRM_Sepamandaat_Config_SepaMandaat {
  
  protected static $_singleton;
  
  protected $customgroup;
  
  protected $fields;
  
  protected function __construct() {
    $cfsp = CRM_Spgeneric_CustomField::singleton();
    $this->customgroup = $cfsp->getGroupByName('SEPA_Mandaat');
    $this->fields = $cfsp->getFields('SEPA_Mandaat');
  }
  
  /**
   * @return CRM_Sepamandaat_Config_SepaMandaat
   */
  
  public static function singleton() {
    if (!self::$_singleton) {
      self::$_singleton = new CRM_Sepamandaat_Config_SepaMandaat();
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

