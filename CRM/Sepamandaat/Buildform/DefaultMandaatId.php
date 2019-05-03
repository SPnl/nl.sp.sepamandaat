<?php

class CRM_Sepamandaat_Buildform_DefaultMandaatId {
  
  protected $form;
  
  public function __construct(&$form) {
    $this->form = $form;
  }

  public function parse() {
    if (!self::isValidForm($this->form)) {
      return;
    }

    $config = CRM_Sepamandaat_Config_SepaMandaat::singleton();
    $cid = $_SESSION['contact_id_custom_sepa'];
    if ($cid) {
      $customFieldName = 'custom_'.$config->getCustomField('mandaat_nr', 'id');

      // get suffix of the new custom field (can be _-1, _-2 etc)
      foreach ($this->form->_elementIndex as $k => $f) {
        if (substr($k, 0, strlen($customFieldName)) == $customFieldName) {
          // check the suffix contains _-
          $i = strpos($k, '_-', 0);
          if ($i === FALSE) {
            // existing item, skip
            break;
          }
          else {
            $suffix = substr($k, $i);

            // mandate ID
            $mandaat_id = CRM_Sepamandaat_SepaMandaat::getNewMandaatIdForContact($cid);
            $field = $customFieldName . $suffix;
            $defaults[$field] = $mandaat_id;

            // status
            $customFieldName = 'custom_'.$config->getCustomField('status', 'id');
            $field = $customFieldName . $suffix;
            $defaults[$field] = 'RCUR';

            $this->form->setDefaults($defaults);
          }
        }
      }
    }
  }
  
  public static function isValidForm($form) {
    $config = CRM_Sepamandaat_Config_SepaMandaat::singleton();
    if ($config->getCustomGroupInfo('id') == $form->_groupID) {
      return true;
    }
    return false;
  }
  
}

