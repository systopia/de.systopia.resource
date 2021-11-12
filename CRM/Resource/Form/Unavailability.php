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
 * Create new Unavailability form
 */
class CRM_Resource_Form_Unavailability extends CRM_Core_Form
{
    /** @var integer resource id */
    protected $resource_id = null;

    /** @var array list of unavailability_types */
    protected $unavailability_types;

    public function buildQuickForm()
    {
        $this->resource_id = CRM_Utils_Request::retrieve('resource_id', 'Integer', $this, true);

        // gather information of the unavailabilities
        $this->unavailability_types = CRM_Resource_BAO_ResourceUnavailability::getAllUnavailabilityTypes();
        $unavailability_type2label = [];
        $type_fields = [];
        foreach ($this->unavailability_types as $unavailability_type) {
            // get the labels
            $unavailability_type2label[$unavailability_type] = call_user_func([$unavailability_type, 'getTypeLabel']);

            // add the fields
            $new_fields = call_user_func([$unavailability_type, 'addFormFields'], $this, $unavailability_type . '_');
            $type_fields = array_merge($type_fields, $new_fields);
        }
        $this->assign('type_fields', $type_fields);

        // add form elements
        $this->add(
            'text',
            'reason',
            E::ts("Reason"),
            ['class' => 'huge'],
            true
        );

        $this->add(
            'select',
            'unavailability_type',
            E::ts("Type"),
            $unavailability_type2label,
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

        Civi::resources()->addScriptUrl(E::url('js/unavailability_create.js'), 10, 'page-header');

        parent::buildQuickForm();
    }

    /**
     * Validate the user defined fields
     *
     * @return bool
     */
    public function validate()
    {
        $unavailability_type = $this->_submitValues['unavailability_type'];
        if (in_array($unavailability_type, $this->unavailability_types)) {
            $errors = call_user_func([$unavailability_type, 'validateFormSubmission'], $this->_submitValues, $unavailability_type . '_');
            foreach ($errors as $field_key => $error) {
                $this->_errors[$field_key] = $error;
            }
        }

        return (0 == count($this->_errors));
    }


    public function postProcess()
    {
        $values = $this->exportValues();

        // create new unavailability
        $unavailability_type = $values['unavailability_type'];
        if (in_array($unavailability_type, $this->unavailability_types)) {
            $parameters = call_user_func([$unavailability_type, 'compileParameters'], $values, $unavailability_type . '_');
            civicrm_api4('ResourceUnavailability', 'create', [
                'values' => [
                    'reason' => $values['reason'],
                    'resource_id' => $this->resource_id,
                    'class_name' => $unavailability_type,
                    'parameters' => json_encode($parameters),
                ],
            ]);
        } else {
            throw new Exception("Unknown unavailability type " . $unavailability_type);
        }

        parent::postProcess();
    }
}
