<?php

/**
 * SepaMandaat.CorrectMandaatIds API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_sepa_mandaat_correct($params) {
    $returnValues = array();
    
    $objects = CRM_Odoosync_Objectlist::singleton();
    
    $config = CRM_Sepamandaat_Config_SepaMandaat::singleton();
    $membership = CRM_Sepamandaat_Config_MembershipSepaMandaat::singleton();
    $contribution = CRM_Sepamandaat_Config_ContributionSepaMandaat::singleton();
    $mandaat_id_field = $config->getCustomField('mandaat_nr', 'column_name');
    $dao = CRM_Core_DAO::executeQuery("SELECT * FROM `".$config->getCustomGroupInfo('table_name')."` WHERE LENGTH(`".$mandaat_id_field."`) >= 34 AND `".$mandaat_id_field."` LIKE 'MBOA-%'  LIMIT 0,1000");
    while($dao->fetch()) {
        $current_mndt_id = $dao->$mandaat_id_field;
        $splitted = explode("-", $current_mndt_id);
        foreach($splitted as $k=> $part) {
            $splitted[$k] = ltrim($part, "0");
        }
        $new_mandaat_id = implode("-", $splitted);
        
        CRM_Core_DAO::executeQuery("UPDATE `".$config->getCustomGroupInfo('table_name')."` SET `".$mandaat_id_field."` = '".$new_mandaat_id."' WHERE `id` = '".$dao->id."'");
        
        $objects->post('edit',$config->getCustomGroupInfo('table_name'), $dao->id);
        
        CRM_Core_DAO::executeQuery("UPDATE `".$contribution->getCustomGroupInfo('table_name')."` SET `".$contribution->getCustomField('mandaat_id', 'column_name')."` = '".$new_mandaat_id."' WHERE `".$contribution->getCustomField('mandaat_id', 'column_name')."` = '".$current_mndt_id."'");
        CRM_Core_DAO::executeQuery("UPDATE `".$membership->getCustomGroupInfo('table_name')."` SET `".$membership->getCustomField('mandaat_id', 'column_name')."` = '".$new_mandaat_id."' WHERE `".$membership->getCustomField('mandaat_id', 'column_name')."` = '".$current_mndt_id."'");
    }

    // Spec: civicrm_api3_create_success($values = 1, $params = array(), $entity = NULL, $action = NULL)
    return civicrm_api3_create_success($returnValues, $params, 'SepaMandaat', 'Correct');
}

