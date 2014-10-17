<?php

/**
 * General config options
 */
class CRM_Sepamandaat_Config {
  
  protected static $_singleton;
  
  protected $isIbanEnabled = false;
  
  protected $isOdooSyncEnabled = false;
  
  protected function __construct() {
    $this->isIbanEnabled = $this->checkForIbanExtension();
    $this->isOdooSyncEnabled = $this->checkForOdooSyncExtension();
  }
  
  /**
   * @return CRM_Sepamandaat_Config
   */
  public static function singleton() {
    if (!self::$_singleton) {
      self::$_singleton = new CRM_Sepamandaat_Config();
    }
    return self::$_singleton;
  }
  
  /**
   * Returns wether the org.civicoop.iban functionality is available
   */
  public function isIbanEnabled() {
    return $this->isIbanEnabled;
  }
  
  /**
   * Returns wether the org.civicoop.odoosync functionality is available
   */
  public function isOdooSyncEnabled() {
    return $this->isOdooSyncEnabled;
  }
  
  protected function checkForIbanExtension() {
    return $this->checkExtension('org.civicoop.ibanaccounts');
  }
  
  protected function checkForOdooSyncExtension() {
    if (!$this->checkExtension('org.civicoop.odoosync')) {
      return false;
    }
    if (!$this->checkExtension('org.civicoop.ibanodoosync')) {
      return false;
    }
    if (!$this->isIbanEnabled()) {
      return false;
    }
    return true;
  }
  
  protected function checkExtension($extension) {
    $error = false;
    $requiredExtensions = array($extension);
    try {
      $extensions = civicrm_api3('Extension', 'get');  
      foreach($extensions['values'] as $ext) {
        if ($ext['status'] == 'installed') {
          if (in_array($ext['key'], $requiredExtensions)) {
            $key = array_search($ext['key'], $requiredExtensions);
            unset($requiredExtensions[$key]);
          }
        }
      }    
    } catch (Exception $e) {
      $error = true;
    }
  
    if ($error || count($requiredExtensions) > 0) {
      return false;
    }
  
    return true;
  }
  
}

