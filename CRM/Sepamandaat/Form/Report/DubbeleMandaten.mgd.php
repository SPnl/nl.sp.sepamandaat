<?php
// This file declares a managed database record of type "ReportTemplate".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// http://wiki.civicrm.org/confluence/display/CRMDOC42/Hook+Reference
return array (
  0 =>
    array (
      'name' => 'CRM_Sepamandaat_Form_Report_DubbeleMandaten',
      'entity' => 'ReportTemplate',
      'params' =>
        array (
          'version' => 3,
          'label' => 'Dubbele mandaten',
          'description' => 'Toont dubbele mandaten (nl.sp.reports)',
          'class_name' => 'CRM_Sepamandaat_Form_Report_DubbeleMandaten',
          'report_url' => 'nl.sp.sepamandaat/dubbelemandaten',
          'component' => '',
        ),
    ),
);