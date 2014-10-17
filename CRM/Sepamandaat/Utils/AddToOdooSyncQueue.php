<?php

/**
 * Delegate of hook_civicrm_custom
 * 
 * This class will add mandaat to the odoo sync queue
 * 
 */
class CRM_Sepamandaat_Utils_AddToOdooSyncQueue {
  
  public static function custom($op,$groupID, $entityID, &$params ) {    
    //check if the group is the Sepa mandaat group
    $mandaat = CRM_Sepamandaat_Config_SepaMandaat::singleton();
    if ($groupID != $mandaat->getCustomGroupInfo('id')) {
      return;
    }

    //check if odoo sync is enable
    $config = CRM_Sepamandaat_Config::singleton();
    if (!$config->isOdooSyncEnabled()) {
      return;
    }
    
    //ok, requirements met
    //add mandaat to odoo sync queue
    if ($op == 'delete') {
      //when deleting the params contains the id
      $objectId = $params;
    } else {
      //first find the id for this custom value pair
      $contactParams = array();
      $contactParams['id'] = $entityID;
      foreach($params as $param) {
        $contactParams['custom_'.$param['custom_field_id']] = $param['value'];
        $contactParams['return.custom_'.$param['custom_field_id']] = 1;
      }
      $contact = civicrm_api3('Contact', 'getsingle', $contactParams);
      //extract the custom value table id
      $objectId = $contact[$mandaat->getCustomGroupInfo('table_name').'_id'];
    }

    
    $objects = CRM_Odoosync_Objectlist::singleton();
    $objects->post($op,$mandaat->getCustomGroupInfo('table_name'), $objectId);
  }
}
