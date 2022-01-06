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
class CRM_Resource_Form_ResourceDemandCreate extends CRM_Core_Form
{
    /** @var integer related entity ID */
    protected $entity_id = null;

    /** @var integer related entity table */
    protected $entity_table = null;

    public function buildQuickForm()
    {
        $this->entity_id = CRM_Utils_Request::retrieve('entity_id', 'Integer', $this);
        $this->entity_table = CRM_Utils_Request::retrieve('entity_table', 'String', $this);

        // add form elements
        $this->add(
            'text',
            'resource_demand_name',
            E::ts('Label'),
            ['placeholder' => E::ts("Resource Label")],
            true
        );

        $this->add(
            'select',
            'resource_type',
            E::ts('Type'),
            $this->getResourceTypes(),
            true,
            ['class' => 'crm-select2']
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
            'resource_count' => 1,
        ]);

        $this->addButtons([
              [
                  'type' => 'submit',
                  'name' => E::ts('Create Resource Demand'),
                  'icon' => 'fa-magic',
                  'isDefault' => true,
              ],
          ]);

        // add some data
        $this->assign('entity_name', CRM_Resource_Types::getEntityName($this->entity_table));

        parent::buildQuickForm();
    }
    /**
     * Get the resource types for the given entity_table
     *
     * @return array
     *  resource types
     */
    protected function getResourceTypes()
    {
        $resource_types = CRM_Resource_Types::getAll();
        $resource_type_options = [];
        foreach ($resource_types as $resource_type) {
            $resource_type_options[$resource_type['id']] = $resource_type['label'];
        }
        return $resource_type_options;
    }


    public function postProcess()
    {
        $values = $this->exportValues();
        civicrm_api3('ResourceDemand', 'create', [
            'entity_id' => $this->entity_id,
            'entity_table' => $this->entity_table,
            'count' => $values['resource_count'],
            'label' => $values['resource_demand_name'],
            'resource_type_id' => $values['resource_type']
        ]);
        parent::postProcess();
    }

}
