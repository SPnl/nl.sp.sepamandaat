<?php

/**
 * Collection of upgrade steps
 */
class CRM_Sepamandaat_Upgrader extends CRM_Sepamandaat_Upgrader_Base {

  
  public function install() {
    $this->executeCustomDataFile('xml/mandaat.xml');
    $this->executeCustomDataFile('xml/contribution_mandaat.xml');
    $this->executeCustomDataFile('xml/membership_mandaat.xml');
  }

  public function upgrade_1001() {
    $this->executeCustomDataFile('xml/mandaat.xml');
    return true;
  }
  
  protected function removeCustomGroup($name) {
    try {
      $custom_group = civicrm_api3('CustomGroup', 'getsingle', array('name' => $name));
      $fields = civicrm_api3('CustomField', 'get' , array('custom_group_id' => $custom_group['id']));
      foreach($fields['values'] as $field) {
        civicrm_api3('CustomField', 'delete', array('id' => $field['id']));
      }
      civicrm_api3('CustomGroup', 'delete', array('id' => $custom_group['id']));
    } catch (Exception $e) {
      
    }
  }

}
