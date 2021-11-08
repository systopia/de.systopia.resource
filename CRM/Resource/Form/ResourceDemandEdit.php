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
class CRM_Resource_Form_ResourceDemandEdit extends CRM_Core_Form
{
    /** @var integer related entity ID */
    protected $id = null;

    /** @var array resource_demand */
    protected $resource_demand = null;

    public function buildQuickForm()
    {
        $this->id = CRM_Utils_Request::retrieve('id', 'Integer', $this);
        $this->resource_demand = civicrm_api3('ResourceDemand', 'getsingle', ['id' => $this->id]);

        // add form elements
        $this->add(
            'text',
            'resource_demand_name',
            E::ts('Label'),
            ['placeholder' => E::ts("Resource Label")],
            true
        );

        $this->add(
            'text',
            'resource_count',
            E::ts('Count'),
            true,
            true
        );
        $this->addRule('resource_count', E::ts('The demand should require a least one resource'), 'positiveInteger');

        $this->setDefaults([
           'resource_count' => $this->resource_demand['count'],
           'resource_demand_name' => $this->resource_demand['label'],
       ]);

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
        civicrm_api3('ResourceDemand', 'create', [
            'id' => $this->id,
            'count' => $values['resource_count'],
            'label' => $values['resource_demand_name'],
        ]);
        parent::postProcess();
    }


}
