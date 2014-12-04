<?php

class CRM_Sepamandaat_Utils_DefaultMandaatId {
  
  public static function validateForm( $formName, &$fields, &$files, &$form, &$errors ) {
    
    if ($formName != 'CRM_Contact_Form_CustomData') {
      return;
    }
    
    $groupID = $form->_groupID;
    $mandaat_config = CRM_Sepamandaat_Config_SepaMandaat::singleton();
    if ($groupID != $mandaat_config->getCustomGroupInfo('id')) {
      return;
    }
    $mandaat_field = 'custom_'.$mandaat_config->getCustomField('mandaat_nr', 'id');
    $params = $form->controller->exportValues($form->getVar('_name'));
    $cid = $form->_tableID;
    $count_from_db = true;
    foreach($params as $key => $value) {
      if (strpos($key, $mandaat_field)===0 && empty($value)) {
        $mandaat_id = CRM_Sepamandaat_SepaMandaat::getNewMandaatIdForContact($cid, $count_from_db);
        $count_from_db = false;
        $params[$key] = $mandaat_id;
        
        $data = &$form->controller->container();              
        $data['values']['CustomData'][$key] = $mandaat_id;
      }
    }
  }
  
  public static function pre($op, $objectName, $objectId, &$params) {
    if ($op != 'edit') {
      return;
    }
    
    if ($objectName != 'Individual' && $objectName != 'Organization' && $objectName != 'Household') {
      return;
    }
    
    $mandaat_fields_sets = array();
    $mandaat = CRM_Sepamandaat_Config_SepaMandaat::singleton();
    foreach($mandaat->getAllCustomFields() as $field) {
      if (isset($params['custom_'.$field['id']])) {
        foreach($params['custom'][$field['id']] as $key => $instance) {
          $mandaat_fields_sets[$key] = $instance['id'];
        }
        break;
      }
    }
    
    if (!count($mandaat_fields_sets)) {
      return;
    }
    
    foreach($mandaat_fields_sets as $key => $id) {
      $mandaat_nr_field_id = $mandaat->getCustomField('mandaat_nr', 'id');
      if (!isset($params['custom'][$mandaat_nr_field_id][$key])) {
        $mandaat_id = '';
        if (!empty($id)) {
          $mandaat_id = CRM_Sepamandaat_SepaMandaat::getMandatesByContactAndId($objectId, $id);
        }
        $mandaat_id_field = array();
        CRM_Core_BAO_CustomField::formatCustomField(    
          $mandaat->getCustomField('mandaat_nr', 'id'),
          $mandaat_id_field,
          $mandaat_id,
          null
        );
        
        $params['custom'][$mandaat_nr_field_id][$key] = $mandaat_id_field[$mandaat_nr_field_id][$key];
      }
      
      if (empty($params['custom'][$mandaat_nr_field_id][$key]['value'])) {
        $params['custom'][$mandaat_nr_field_id][$key]['value'] = CRM_Sepamandaat_SepaMandaat::getNewMandaatIdForContact($objectId);
      }
    }
  }
  
  public static function custom($op,$groupID, $entityID, &$params ) {
    if ($op != 'create' && $op != 'edit') {
      return;
    }
    
    //check if the group is the Sepa mandaat group
    $mandaat = CRM_Sepamandaat_Config_SepaMandaat::singleton();
    if ($groupID != $mandaat->getCustomGroupInfo('id')) {
      return;
    }
    
    $mandaat_config = CRM_Sepamandaat_Config_SepaMandaat::singleton();

    foreach($params as $key => $param) {
      if ($param['custom_field_id'] == $mandaat_config->getCustomField('mandaat_nr', 'id') && empty($param['value'])) {
        $mandaat_id = CRM_Sepamandaat_SepaMandaat::getNewMandaatIdForContact($param['entity_id']);
        $params[$key]['value'] = $mandaat_id;
        if (!empty($param['id'])) {
          $sql = "UPDATE `".$params[$key]['table_name']."` SET `".$params[$key]['column_name']."` = %1 WHERE `id` = %2";
          CRM_Core_DAO::executeQuery($sql, array(
            1 => array($params[$key]['value'], 'String'),
            2 => array($params[$key]['id'], 'Integer'),
          ));
        }
      }
    }
  }
}

