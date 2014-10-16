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

}
