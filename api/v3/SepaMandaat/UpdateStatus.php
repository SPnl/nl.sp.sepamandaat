<?php

/**
 * IbanAccount.Get API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_sepa_mandaat_update_status_spec(&$spec) {
  $spec['mandaat_odoo_id']['api.required'] = 1;
  $spec['status']['api.required'] = 1;
}

function civicrm_api3_sepa_mandaat_update_status($params) {
  if (array_key_exists('mandaat_odoo_id', $params) && array_key_exists('status', $params)) {
    $civiIds = CRM_Odoosync_Model_OdooEntity::findByOdooId('sdd.mandate', $params['mandaat_odoo_id']);
    $config = CRM_Sepamandaat_Config_SepaMandaat::singleton();
    foreach($civiIds as $id => $entity) {
      try {
        $sql = "UPDATE `".$config->getCustomGroupInfo('table_name')."` SET `".$config->getCustomField('status', 'column_name')."` = %1 WHERE `id` = %2";
        CRM_Core_DAO::executeQuery($sql, array(
          1 => array($params['status'], 'String'),
          2 => array($id, 'Integer'),
        ));
      } catch (Exception $ex) {

      }
    }

    return civicrm_api3_create_success($mandates, $params, 'SepaMandaat', 'get');
  } else {
    throw new API_Exception('Contact ID required');
  }
}
