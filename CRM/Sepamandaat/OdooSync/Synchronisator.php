<?php

class CRM_Sepamandaat_OdooSync_Synchronisator extends CRM_Odoosync_Model_ObjectSynchronisator {
  
  /**
   *
   * @var CRM_Sepamandaat_Config_SepaMandaat
   */
  protected $config;
  
  public function __construct(CRM_Odoosync_Model_ObjectDefinitionInterface $objectDefinition) {
    $this->config = CRM_Sepamandaat_Config_SepaMandaat::singleton();
    parent::__construct($objectDefinition);
  }
  
  /**
   * Retruns wether this item is syncable
   * By default false. 
   * 
   * subclasses should implement this function to make items syncable
   */
  public function isThisItemSyncable(CRM_Odoosync_Model_OdooEntity $sync_entity) {
    return true;
  }
  
  /**
   * Insert a civicrm entity into Odoo
   * 
   */
  public function performInsert(CRM_Odoosync_Model_OdooEntity $sync_entity) {
    $data = $this->getSepaMandaat($sync_entity->getEntityId());
    $odoo_partner_id = $sync_entity->findOdooIdByEntity('civicrm_contact', $data['contact_id']);
    $parameters = $this->getOdooParameters($data, $odoo_partner_id, $sync_entity->getEntity(), $sync_entity->getEntityId(), 'create', $sync_entity);
    $odoo_id = $this->connector->create($this->getOdooResourceType(), $parameters);
    if ($odoo_id) {
      return $odoo_id;
    } 
    throw new Exception('Could not insert bank sepa mandaat into Odoo');
  }
  
  /**
   * Update an Odoo resource with civicrm data
   * 
   */
  public function performUpdate($odoo_id, CRM_Odoosync_Model_OdooEntity $sync_entity) {
    $data = $this->getSepaMandaat($sync_entity->getEntityId());
    $odoo_partner_id = $sync_entity->findOdooIdByEntity('civicrm_contact', $data['contact_id']);
    $parameters = $this->getOdooParameters($data, $odoo_partner_id, $sync_entity->getEntity(), $sync_entity->getEntityId(), 'create', $sync_entity);
    if ($this->connector->write($this->getOdooResourceType(), $odoo_id, $parameters)) {
      return $odoo_id;
    }
    throw new Exception("Could not update sepa mandaat in Odoo");
  }
  
  public function getSyncData(\CRM_Odoosync_Model_OdooEntity $sync_entity, $odoo_id) {
    $data = $this->getSepaMandaat($sync_entity->getEntityId());
    $odoo_partner_id = $sync_entity->findOdooIdByEntity('civicrm_contact', $data['contact_id']);
    $parameters = $this->getOdooParameters($data, $odoo_partner_id, $sync_entity->getEntity(), $sync_entity->getEntityId(), 'create', $sync_entity);
    return $parameters;
  }
  
  /**
   * Delete an item from Odoo
   * 
   */
  function performDelete($odoo_id, CRM_Odoosync_Model_OdooEntity $sync_entity) {
    if ($this->connector->unlink($this->getOdooResourceType(), $odoo_id)) {
      return -1;
    }
    throw new Exception('Could not delete sepa mandaat from Odoo');
  }
  
  /**
   * Find item in Odoo and return odoo_id
   * 
   */
  public function findOdooId(CRM_Odoosync_Model_OdooEntity $sync_entity) {
    $data = $this->getSepaMandaat($sync_entity->getEntityId());
    $odoo_partner_id = $sync_entity->findOdooIdByEntity('civicrm_contact', $data['contact_id']);
        
    $key = array(
      new xmlrpcval(array(
        new xmlrpcval('partner_id', 'string'),
        new xmlrpcval('=', 'string'),
        new xmlrpcval($odoo_partner_id, 'int'),
      ), "array"),
      new xmlrpcval(array(
        new xmlrpcval('unique_mandate_reference', 'string'),
        new xmlrpcval('=', 'string'),
        new xmlrpcval($data['mandaat_nr'], 'string'),
      ), "array")
    );
    
    
    $result = $this->connector->search($this->getOdooResourceType(), $key);
    foreach($result as $id_element) {
        $id = $id_element->scalarval();
    }
    return false;
  }
  
  /**
   * Checks if an entity still exists in CiviCRM.
   * 
   * This is used to check wether a civicrm entity is soft deleted or hard deleted. 
   * In the first case we have to update the entity in odoo 
   * In the second case we have to delete the entity from odoo 
   */
  public function existsInCivi(CRM_Odoosync_Model_OdooEntity $sync_entity) {
    $table = $this->config->getCustomGroupInfo('table_name');
    $dao = CRM_Core_DAO::executeQuery("SELECT * FROM `".$table."` WHERE `id` = %1", array(
      1 => array($sync_entity->getEntityId(), 'Integer'),
    ));
    
    if ($dao->fetch()) {
      return true;
    }
    return false;
  }
  
  /**
   * Returns the name of the Odoo resource e.g. res.partner
   * 
   * @return string
   */
  public function getOdooResourceType() {
    return 'sdd.mandate';
  }
  
  protected function getSepaMandaat($entity_id) {
    return $this->objectDefinition->getCiviCRMEntityDataById($entity_id);
  }
  
  /**
   * Returns the parameters to update/insert an Odoo object
   * 
   * @param type $contact
   * @return \xmlrpcval
   */
  protected function getOdooParameters($data, $odoo_partner_id, $entity, $entity_id, $action, $sync_entity) {
    $mandaat_datum = new DateTime($data['mandaat_datum']);
    $iban_config = CRM_Ibanaccounts_Config::singleton();
    $bank_id = $sync_entity->findOdooIdByEntity($iban_config->getIbanCustomGroupValue('table_name'), $data['iban_id']);
    $parameters = array(
      'partner_bank_id' => new xmlrpcval($bank_id, 'int'),
      'partner_id' => new xmlrpcval($odoo_partner_id, 'int'),  
      'signature_date' => new xmlrpcval($mandaat_datum->format('m-d-Y'), 'string'),
      'unique_mandate_reference' => new xmlrpcval($data['mandaat_nr'], 'string'),
    );
    if ($parameters['status'] == 'OOFF') {
      $parameters['type'] = new xmlrpcval('oneoff', 'string');
      $parameters['recurrent_sequence_type'] = new xmlrpcval('first', 'string');
    } else {
      $parameters['type'] = new xmlrpcval('recurrent', 'string');
      $conversion = array('FRST' => 'first', 'RCUR' => 'recurring');
      $recurType = 'first';
      if (isset($conversion[$data['status']])) {
        $recurType = $conversion[$data['status']];
      }
      $parameters['recurrent_sequence_type'] = new xmlrpcval($recurType, 'string');
    }
    
    $parameters['state'] = new xmlrpcval('valid', 'string');
    
    $this->alterOdooParameters($parameters, $this->getOdooResourceType(), $entity, $entity_id, $action);
    
    return $parameters;
  }
  
}
