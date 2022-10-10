<?php
/*-------------------------------------------------------+
| SYSTOPIA Resource Framework                            |
| Copyright (C) 2022 SYSTOPIA                            |
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
class CRM_Resource_Form_ResourceEdit extends CRM_Core_Form
{
    /** @var integer related entity ID */
    protected $id = null;

    /** @var array resource */
    protected $resource = null;

    public function buildQuickForm()
    {
        $this->id = CRM_Utils_Request::retrieve('resource_id', 'Integer', $this);
        $this->resource = civicrm_api3('Resource', 'getsingle', ['id' => $this->id]);

        $resource_type = CRM_Resource_Types::getType($this->resource['resource_type_id']);
        $this->setDefaults([
           'resource_label' => $this->resource['label'],
           'resource_type' => CRM_Resource_Types::getType($this->resource['resource_type_id'])['label'],
        ]);

        // add resource name field
        $this->add(
            'text',
            'resource_label',
            E::ts('Label'),
            ['placeholder' => E::ts("Resource Label")],
            true
        );

        $this->add(
            'text',
            'resource_type',
            E::ts('Type'),
            ['placeholder' => E::ts("Resource Type"), 'frozen'],
            true
        )->freeze();

        $this->addButtons([
            [
              'type' => 'submit',
              'name' => E::ts('Save'),
              'icon' => 'fa-floppy-o',
              'isDefault' => true,
            ],
        ]);

        parent::buildQuickForm();
    }

    public function postProcess()
    {
        $values = $this->exportValues();
        civicrm_api3('Resource', 'create', [
            'id' => $this->id,
            'label' => $values['resource_label'],
        ]);
        parent::postProcess();
    }

}
