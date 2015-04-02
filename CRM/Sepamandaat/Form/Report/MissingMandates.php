<?php

class CRM_Sepamandaat_Form_Report_MissingMandates extends CRM_Report_Form {

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
    $this->assign('reportTitle', ts('Missing mandates'));
    parent::preProcess();
  }

  function buildQuery($applyLimit = TRUE) {
    $payment_instrument_id = CRM_Core_DAO::singleValueQuery("SELECT v.value from civicrm_option_value v inner join civicrm_option_group g on v.option_group_id = g.id where g.name = 'payment_instrument' and v.name = 'sp_automatischincasse';");

    $cconfig = CRM_Sepamandaat_Config_ContributionSepaMandaat::singleton();
    $cfield = $cconfig->getCustomField('mandaat_id', 'column_name');
    $ctable = $cconfig->getCustomGroupInfo('table_name');

    $config = CRM_Sepamandaat_Config_SepaMandaat::singleton();
    $field = $config->getCustomField('mandaat_nr', 'column_name');
    $table = $config->getCustomGroupInfo('table_name');

    if ($applyLimit) {
      $this->limit();
    }

    return "SELECT SQL_CALC_FOUND_ROWS distinct m.id as id, c.contact_id as contact_id , contact.display_name as contact_name, mt.name as membership
              from civicrm_contribution c
              left join civicrm_contact contact on c.contact_id = contact.id
              left join civicrm_membership_payment mp on c.id = mp.contribution_id
              left join civicrm_membership m on mp.membership_id = m.id
              left join civicrm_membership_type mt on m.membership_type_id = mt.id
              left join `".$ctable."` `cm` ON `c`.`id` = `cm`.`entity_id`
              left join `".$table."` `mandaat` ON `cm`.`".$cfield."` = `mandaat`.`".$field."`
              where c.payment_instrument_id = '".$payment_instrument_id."'
              and year(date(c.receive_date)) = 2015
              AND (`cm`.`id` IS NULL OR `mandaat`.`id` IS NULL)
              ORDER BY mt.name desc
             {$this->_limit}";
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
    $this->_columnHeaders['contact_id'] = array('title' => 'Contact ID');
    $this->_columnHeaders['contact_name'] = array('title' =>'Name');
    $this->_columnHeaders['membership'] = array('title' =>'Membership');
    $this->_columnHeaders['groups'] = array('title' =>'Group');
  }

  function alterDisplay(&$rows) {
    foreach($rows as $rowNum => $row) {
      $url = CRM_Utils_System::url("civicrm/contact/view",
        'reset=1&cid=' . $row['contact_id'],
        $this->_absoluteUrl
      );
      $rows[$rowNum]['contact_name_link'] = $url;
      $rows[$rowNum]['groups'] = '';
      $group = civicrm_api3('GroupContact', 'get', array('contact_id' => $row['contact_id']));
      foreach ($group["values"] as $g) {
        if (strlen($rows[$rowNum]['groups'])) {
          $rows[$rowNum]['groups'] .= ', ';
        }
        $rows[$rowNum]['groups'] .= $g['title'];
      }
    }
  }
}
