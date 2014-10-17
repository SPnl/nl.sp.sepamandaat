<?php

/**
 * IbanAccount.Get API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_sepa_mandaat_get_spec(&$spec) {
  $spec['contact_id']['api.required'] = 1;
}

/**
 * IbanAccount.Get API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_sepa_mandaat_get($params) {
  if (array_key_exists('contact_id', $params)) {
    $mandates = CRM_Sepamandaat_SepaMandaat::getMandatesByContact($params['contact_id']);

    return civicrm_api3_create_success($mandates, $params, 'SepaMandaat', 'get');
  } else {
    throw new API_Exception('Contact ID required');
  }
}

