<?php
/*-------------------------------------------------------+
| SYSTOPIA Resource Framework                            |
| Copyright (C) 2021 SYSTOPIA                            |
| Author: B. Endres (endres@systopia.de)                 |
+--------------------------------------------------------+
| This program is released as free software under the    |
| Affero GPL license. You can redistribute it and/or     |
| modify it under the terms of this license which you    |
| can read by viewing the included agpl.txt or online    |
| at www.gnu.org/licenses/agpl.html. Removal of this     |
| copyright header is strictly prohibited without        |
| written permission from the original author(s).        |
+--------------------------------------------------------*/

use CRM_Resource_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/quickform/
 */
class CRM_Resource_Form_ConditionEdit extends CRM_Core_Form
{
    /** @var integer condition id */
    protected $condition_id = null;

    /** @var CRM_Resource_BAO_ResourceDemandCondition */
    protected $condition = null;

    /** @var CRM_Resource_BAO_ResourceDemand */
    protected $demand;

    public function buildQuickForm()
    {
        $this->condition_id = CRM_Utils_Request::retrieve('id', 'Integer', $this, true);

        // load condition
        /** @var CRM_Resource_BAO_ResourceDemandCondition  $bao */
        $bao = CRM_Resource_BAO_ResourceDemandCondition::findById($this->condition_id);
        if (empty($bao)) {
            throw new Exception("Unavailability [$this->condition_id] not found.");
        }
        $this->condition = $bao->getImplementation();

        // load demand
        $this->demand = CRM_Resource_BAO_ResourceDemand::findById($this->condition->resource_demand_id);

        // get the condition class
        $condition_types = CRM_Resource_BAO_ResourceDemandCondition::getAllConditionTypes($this->demand->entity_table);
        if (!in_array($this->condition->class_name, $condition_types)) {
            throw new Exception("Undefined condition type: {$this->condition->class_name}");
        }

        // get the labels
        $this->assign('current_label', $this->condition->getLabel());
        $type_fields = [];
        foreach ($condition_types as $condition_type) {
          // get the labels
          $condition_type2label[$condition_type] = call_user_func([$condition_type, 'getTypeLabel']);

          // add the fields
          $new_fields = call_user_func([$condition_type, 'addFormFields'], $this, $condition_type . '_', $this->demand);
          $type_fields = array_merge($type_fields, $new_fields);
        }

        $this->assign('type_fields', $type_fields);
        $excludeLabelsPerID = [
          'CRM_Resource_DemandCondition_Attribute__value',
          'CRM_Resource_DemandCondition_Attribute__multi_value',
        ];
        $this->assign('exclude_labels', $excludeLabelsPerID);

        // Extract the class name
        $conditionClassName = $this->condition->class_name;
        // We'll need this to filter the fields in the template
        $this->assign('condition_type', $conditionClassName);

        $this->add(
          'select',
          'condition_type',
          E::ts("Type"),
          $condition_type2label,
          true,
          ['class' => 'crm-select2']
        );

        $this->addButtons([
              [
                  'type' => 'submit',
                  'name' => E::ts('Update'),
                  'isDefault' => true,
              ],
          ]);

        // Get the defaults
        $this->setDefaults($this->condition->getCurrentFormValues($conditionClassName));

        Civi::resources()->addScriptFile(E::LONG_NAME, 'js/condition_create.js', 10, 'page-header');
        Civi::resources()->addScriptFile(E::LONG_NAME, 'js/condition_rules.js', 10, 'page-header');

        parent::buildQuickForm();
    }

    /**
     * Validate the user defined fields
     *
     * @return bool
     */
    public function validate()
    {
        $errors = call_user_func([$this->condition->class_name, 'validateFormSubmission'], $this->_submitValues);
        foreach ($errors as $field_key => $error) {
            $this->_errors[$field_key] = $error;
        }
        return (0 == count($this->_errors));
    }

    public function postProcess()
    {
        $values = $this->exportValues();
        $condition_type = $values['condition_type'];
        $parameters = call_user_func([$condition_type, 'compileParameters'], $values, $condition_type . '_');
        $this->condition->parameters = json_encode($parameters);
        $this->condition->save();
        parent::postProcess();
    }

}