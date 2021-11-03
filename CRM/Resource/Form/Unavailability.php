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

    public function buildQuickForm()
    {
        $this->resource_id = CRM_Utils_Request::retrieve('resource_id', 'Integer', $this, true);

        // gather information of the unavailabilities
        $unavailability_types = CRM_Resource_BAO_ResourceUnavailability::getAllUnavailabilityTypes();
        $unavailability_type2label = [];
        $type_fields = [];
        foreach ($unavailability_types as $unavailability_type) {
            // get the labels
            $unavailability_type2label[$unavailability_type] = call_user_func([$unavailability_type, 'getTypeLabel']);

            // add the fields
            $new_fields = call_user_func([$unavailability_type, 'addFormFields'], $this, $unavailability_type);
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
            true
        );



        $this->addButtons([
            [
                'type' => 'submit',
                'name' => E::ts('Create'),
                'isDefault' => true,
            ],
        ]);

        parent::buildQuickForm();
    }


    public function postProcess()
    {
        $values = $this->exportValues();

        // create new unavailability
        $unavailability_type = $values['unavailability_type'];
        $parameters = call_user_func([$unavailability_type, 'addFormFields'], $values, $unavailability_type);
        $result = civicrm_api4('ResourceUnavailability', 'create', [
            'values' => [
                'reason' => $values['reason'],
                'resource_id' => $this->resource_id,
                'class_name' => $unavailability_type,
                'parameters' => json_encode($parameters),
            ],
        ]);

        parent::postProcess();
    }

}
