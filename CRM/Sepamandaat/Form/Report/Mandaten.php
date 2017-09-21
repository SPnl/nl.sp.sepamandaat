<?php

class CRM_Sepamandaat_Form_Report_Mandaten extends CRM_Report_Form {
  protected $_addressField = FALSE;
  protected $_emailField = FALSE;
  protected $_summary = NULL;
  protected $_customGroupExtends = array();
  protected $_customGroupGroupBy = FALSE;
  protected $_add2groupSupported = FALSE;
  protected $_noFields = TRUE;

  function __construct() {
    $this->_groupFilter = FALSE;
    $this->_tagFilter = FALSE;
    $this->_columns = array();
    parent::__construct();
  }

  function preProcess() {
    $this->assign('reportTitle', ts('Mandates'));
    parent::preProcess();
  }

  function buildQuery($applyLimit = TRUE) {
    if ($applyLimit) {
      $this->limit();
    }

    $sql = "
      SELECT SQL_CALC_FOUND_ROWS
        contact_a.id contact_id,
        o.odoo_id odoo_contact_id,
        contact_a.sort_name,
        m.status,
        o2.odoo_id odoo_mandaat_id,
        m.mandaat_nr,
        m.subject,
        m.mandaat_datum,
        m.plaats,
        m.verval_datum,
        m.iban,
        m.bic,
        m.tnv
      FROM
        civicrm_value_sepa_mandaat m
      INNER JOIN
        civicrm_contact contact_a on contact_a.id = m.entity_id
      LEFT OUTER JOIN
        civicrm_odoo_entity o on contact_a.id = o.entity_id and o.entity = 'civicrm_contact'
      LEFT OUTER JOIN
        civicrm_odoo_entity o2 on m.id = o2.entity_id and o2.entity = 'civicrm_value_sepa_mandaat'
      ORDER BY
        contact_a.sort_name, m.mandaat_datum
      {$this->_limit}   
    ";

    return $sql;
  }

  function postProcess() {
    $this->beginPostProcess();

    // get the acl clauses built before we assemble the query
    $sql = $this->buildQuery(TRUE);

    $rows = array();
    $this->buildRows($sql, $rows);
    $this->formatDisplay($rows);
    $this->doTemplateAssignment($rows);
    $this->endPostProcess($rows);
  }

  function modifyColumnHeaders() {
    // use this method to modify $this->_columnHeaders
    $this->_columnHeaders['sort_name'] = array('title' => 'Naam');
    $this->_columnHeaders['contact_id'] = array('title' => 'CiviCRM contact ID');
    $this->_columnHeaders['odoo_contact_id'] = array('title' => 'Odoo contact ID');
    $this->_columnHeaders['odoo_mandaat_id'] = array('title' => 'Odoo mandaat ID');
    $this->_columnHeaders['mandaat_nr'] = array('title' => 'Mandaat nummer');
    $this->_columnHeaders['status'] = array('title' => 'Status');
    $this->_columnHeaders['subject'] = array('title' => 'Onderwerp');
    $this->_columnHeaders['mandaat_datum'] = array('title' => 'Datum');
    $this->_columnHeaders['plaats'] = array('title' => 'Plaats');
    $this->_columnHeaders['verval_datum'] = array('title' => 'Vervaldatum');
    $this->_columnHeaders['iban'] = array('title' => 'IBAN');
    $this->_columnHeaders['bic'] = array('title' => 'BIC');
    $this->_columnHeaders['tnv'] = array('title' => 'Ten name van');
  }
}
