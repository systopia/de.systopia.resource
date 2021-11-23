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

class CRM_Resource_Page_ResourceDemandConditions extends CRM_Core_Page
{
    /** @var integer resource demand ID */
    protected $resource_demand_id = null;

    /** @var CRM_Resource_BAO_ResourceDemand resource */
    protected $resource_demand = null;

    /** @var array condition data */
    protected $conditions = null;

    public function run()
    {
        $this->resource_demand_id = CRM_Utils_Request::retrieve('resource_demand_id', 'Integer', $this);

        // load resource demand
        $this->resource_demand = CRM_Resource_BAO_ResourceDemand::getInstance($this->resource_demand_id);

        $resource_type = CRM_Resource_Types::getType($this->resource_demand->resource_type_id);
        $this->assign('demand_type_label', $resource_type['label']);
        $this->assign('demand_label', $this->resource_demand->label);

        CRM_Utils_System::setTitle(E::ts("ResourceDemand '%1' Conditions", [1 => $this->resource_demand->label]));

        // counts
        $this->assign('demand_resources_count', $this->resource_demand->count);
        $this->assign('demand_assigned_count', $this->resource_demand->getAssignmentCount());
        $this->assign('demand_matching_count', $this->resource_demand->getFulfilledCount());

        $this->assign('condition_create_link',
                      CRM_Utils_System::url('civicrm/resource/condition/create', "resource_demand_id={$this->resource_demand_id}"));

        // load and prep conditions
        $conditions = $this->resource_demand->getDemandConditions();
        $condition_list = [];
        foreach ($conditions as $condition) {
            $condition_data = $condition->toArray();
            $condition_data['id'] = $condition->id;
            $condition_data['display_name'] = $condition->getLabel();
            $condition_data['icon'] = $condition->getIcon();
            $condition_data['edit_link'] = CRM_Utils_System::url(
                'civicrm/resource/condition/edit',
                "id={$condition->id}"
            );
            $condition_list[] = $condition_data;
        }
        $this->assign('conditions', $condition_list);

        Civi::resources()->addStyleUrl(E::url('css/resource_demand_conditions.css'), 10, 'page-header');
        Civi::resources()->addScriptUrl(E::url('js/resource_demand_conditions.js'), 10, 'page-header');

        parent::run();
    }
}
