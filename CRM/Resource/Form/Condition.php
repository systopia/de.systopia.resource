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
 * Create new Condition form
 */
class CRM_Resource_Form_Condition extends CRM_Core_Form
{
    /** @var integer resource demand id */
    protected $resource_demand_id = null;

    /** @var \CRM_Resource_BAO_ResourceDemand resource demand */
    protected $resource_demand = null;

    /** @var array list of condition_types */
    protected $condition_types;

    public function buildQuickForm()
    {
        $this->resource_demand_id = CRM_Utils_Request::retrieve('resource_demand_id', 'Integer', $this, true);
        $this->resource_demand = CRM_Resource_BAO_ResourceDemand::getInstance($this->resource_demand_id);

        // gather information of the unavailabilities
        $this->condition_types = CRM_Resource_BAO_ResourceDemandCondition::getAllConditionTypes($this->resource_demand->entity_table);
        $condition_type2label = ['not_selected' => E::ts("- select condition type -")];
        $type_fields = [];
        foreach ($this->condition_types as $condition_type) {
            // get the labels
            $condition_type2label[$condition_type] = call_user_func([$condition_type, 'getTypeLabel']);

            // add the fields
            $new_fields = call_user_func([$condition_type, 'addFormFields'], $this, $condition_type . '_', $this->resource_demand);
            $type_fields = array_merge($type_fields, $new_fields);
        }
        $this->assign('type_fields', $type_fields);

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
                  'name' => E::ts('Create'),
                  'isDefault' => true,
              ],
          ]);

        Civi::resources()->addScriptFile(E::LONG_NAME, 'js/condition_create.js', 10, 'page-header');

        parent::buildQuickForm();
    }

    /**
     * Validate the user defined fields
     *
     * @return bool
     */
    public function validate()
    {
        $condition_type = $this->_submitValues['condition_type'];
        if ($condition_type == 'not_selected') {
            $this->_errors['condition_type'] = E::ts("Please select a condition type");
        } else {
            if (in_array($condition_type, $this->condition_types)) {
                $errors = call_user_func([$condition_type, 'validateFormSubmission'], $this->_submitValues, $condition_type . '_');
                foreach ($errors as $field_key => $error) {
                    $this->_errors[$field_key] = $error;
                }
            }
        }

        return (0 == count($this->_errors));
    }


    public function postProcess()
    {
        $values = $this->exportValues();

        // create new unavailability
        $condition_type = $values['condition_type'];
        if (in_array($condition_type, $this->condition_types)) {
            $parameters = call_user_func([$condition_type, 'compileParameters'], $values, $condition_type . '_');
            civicrm_api4('ResourceDemandCondition', 'create', [
                'values' => [
                    'resource_demand_id' => $this->resource_demand_id,
                    'class_name' => $condition_type,
                    'parameters' => json_encode($parameters),
                ],
            ]);
        } else {
            throw new Exception("Unknown condition type " . $condition_type);
        }

        parent::postProcess();
    }
}
