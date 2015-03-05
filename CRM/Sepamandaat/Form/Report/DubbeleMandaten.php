<?php

class CRM_Sepamandaat_Form_Report_DubbeleMandaten extends CRM_Report_Form {

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
    $this->assign('reportTitle', ts('Odoo Sync Queue'));
    parent::preProcess();
  }
  
  function buildQuery($applyLimit = TRUE) {
    $config = CRM_Sepamandaat_Config_SepaMandaat::singleton();
    $field = $config->getCustomField('mandaat_nr', 'column_name');
    $table = $config->getCustomGroupInfo('table_name');
    return "SELECT `".$field."` as `mandaat`, COUNT(*) AS `total` FROM `".$table."` GROUP BY `".$field."` HAVING COUNT(*) > 1";
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
    $this->_columnHeaders['mandaat'] = array('title' => 'Mandaat');
    $this->_columnHeaders['total'] = array('title' =>'total');
    $this->_columnHeaders['contacten'] = array('title' =>'Contacts');
  }

  function alterDisplay(&$rows) {
    // custom code to alter rows
    $config = CRM_Sepamandaat_Config_SepaMandaat::singleton();
    $field = $config->getCustomField('mandaat_nr', 'column_name');
    $table = $config->getCustomGroupInfo('table_name');
    foreach($rows as $rowNum => $row) {
      $sql = "SELECT c.id, c.display_name from civicrm_contact c inner join `".$table."` m on c.id = m.entity_id where m.`".$field."` = %1";
      $params['1'] = array($row['mandaat'], 'String');
      $dao = CRM_Core_DAO::executeQuery($sql, $params);
      $contact = "";
      while($dao->fetch()) {
        $url = CRM_Utils_System::url("civicrm/contact/view",
          'reset=1&cid=' . $dao->id,
          $this->_absoluteUrl
        );
        if (strlen($contact)) {
          $contact .= "<br />";
        }
        $contact .= '<a href="'.$url.'">'.$dao->display_name.'</a>';
      }
      $rows[$rowNum]['contacten'] = $contact;
    }
  }
}
