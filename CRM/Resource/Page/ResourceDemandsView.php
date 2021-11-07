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

class CRM_Resource_Page_ResourceDemandsView extends CRM_Core_Page
{
    /** @var integer entity ID */
    public $entity_id = null;

    /** @var integer entity table
    protected $entity_table = null;

    /** @var array of CRM_Resource_BAO_ResourceDemand  */
    protected $resource_demands = null;

    /** @var array assignment data */
    protected $assignments = null;

    /** @var object instance to assign variables to, by default this page */
    protected $assign_sink = null;

    /**
     * Set the referred entity, overrides anything retrieved from the url/post
     * @param string $entity_table
     * @param integer $entity_id
     */
    public function setEntity($entity_table, $entity_id)
    {
        $this->entity_id = $entity_id;
        $this->entity_table = $entity_table;
    }

    public function run()
    {
        $this->assignData();
        parent::run();
    }

    public function assignData()
    {
        // init basics
        if (!isset($this->data_sink))
            $this->data_sink = $this;
        if (!isset($this->entity_id))
            $this->entity_id = CRM_Utils_Request::retrieve('entity_id', 'Integer', $this);
        if (!isset($this->entity_table))
            $this->entity_table = CRM_Utils_Request::retrieve('entity_table', 'String', $this);

        // fetch all resource demands
        $this->resource_demands = CRM_Resource_BAO_ResourceDemand::getResourceDemandsFor($this->entity_id, $this->entity_table);

        // render resource demands
        $resource_demand_list = [];
        $demands_met_count = 0;
        foreach ($this->resource_demands as $resource_demand_bao) {
            /** @var CRM_Resource_BAO_ResourceDemand $resource_demand */
            $resource_demand = $resource_demand_bao->toArray();
            $resource_demand['id'] = $resource_demand_bao->id;
            $resource_demand['is_met'] = !$resource_demand_bao->currentlyUnfulfilled();
            $resource_demand['type_label'] = CRM_Resource_Types::getType($resource_demand['resource_type_id'])['label'];
            $resource_demand['assign_link'] = CRM_Utils_System::url('civicrm/resource/demand/assign', "reset=1&resource_demand_id={$resource_demand_bao->id}");
            $resource_demand['edit_link'] = CRM_Utils_System::url('civicrm/resource/demand/edit', "reset=1&id={$resource_demand_bao->id}");
            $resource_demand['assignment_count'] = $resource_demand_bao->getAssignmentCount(CRM_Resource_BAO_ResourceAssignment::STATUS_CONFIRMED);
            $resource_demand['condition_count'] = 0; // todo
            $demands_met_count += $resource_demand['is_met'] ? 1 : 0;
            $resource_demand_list[$resource_demand_bao->id] = $resource_demand;
        }
        $this->assign('resource_demand_data',$resource_demand_list);
        $this->assign('demand_count', count($resource_demand_list));
        $this->assign('demands_met_count', $demands_met_count);

        // add links
        $this->assign('create_resource_requirement_link',
            CRM_Utils_System::url('civicrm/resource/demand/create', "reset=1&entity_id={$this->entity_id}&entity_table={$this->entity_table}"));

        Civi::resources()->addStyleUrl(E::url('css/resource_demands_view.css'));
        Civi::resources()->addScriptUrl(E::url('js/resource_demands_view.js'));
    }

    /**
     * Set a custom assign() sink
     */
    public function setAssignSink($assign_sink) {
        $this->assign_sink = $assign_sink;
    }

}
