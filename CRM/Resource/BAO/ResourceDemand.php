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

class CRM_Resource_BAO_ResourceDemand extends CRM_Resource_DAO_ResourceDemand
{
    /**
     * Create a new ResourceDemand based on array-data
     *
     * @param array $params key-value pairs
     *
     * @return CRM_Resource_DAO_ResourceDemand|NULL
     */
    public static function create($params)
    {
        // call pre hook
        $className = 'CRM_Resource_BAO_ResourceDemand';
        $entityName = 'ResourceDemand';
        $hook = empty($params['id']) ? 'create' : 'edit';
        CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);

        // create instance
        $instance = new $className();
        $instance->copyValues($params);
        $instance->save();

        // call post hook
        CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);
        return $instance;
    }

    /**
     * Check if all given conditions are currently met by the given resource
     *
     * @param \CRM_Resource_BAO_Resource $resource
     *
     * @return boolean does the resource fulfill the required conditions
     */
    public function isFulfilledWithResource($resource, $cached = true)
    {
        $demand_conditions = $this->getDemandConditions($cached);
        foreach ($demand_conditions as $demand_condition) {
            /** @var $demand_condition CRM_Resource_BAO_ResourceDemandCondition */
            if (!$demand_condition->isFulfilledWithResource($resource)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Check if all conditions are currently met
     *
     * @param int|array $assignment_status
     *    the status of the assignments to be considered
     *
     * @return integer
     *    number of resources missing / not fulfilled
     */
    public function currentlyUnfulfilled($assignment_status = [CRM_Resource_BAO_ResourceAssignment::STATUS_CONFIRMED])
    {
        $unfulfilled = $this->count;
        $linked_resources = $this->getAssignedResources($assignment_status);
        foreach ($linked_resources as $linked_resource) {
            if ($this->isFulfilledWithResource($linked_resource)) {
                $unfulfilled -= 1;
            }
        }
        return $unfulfilled;
    }

    /**
     * Get all the assigned resources
     *
     * @param int|array $assignment_status
     *    the status of the assignments to be considered
     *
     * @return array list of CRM_Resource_BAO_Resources
     */
    public function getAssignedResources($assignment_status = [CRM_Resource_BAO_ResourceAssignment::STATUS_CONFIRMED])
    {
        if (!is_array($assignment_status)) {
            $assignment_status = [$assignment_status];
        }

        // make sure they're all integers
        $assignment_status = array_map('intval', $assignment_status);

        // get the linked resource(s)
        $resource_list = civicrm_api4('Resource', 'get', [
            'select' => ['id'],
            'join' => [
                ['ResourceAssignment AS resource_assignment', true, null, ['id', '=', 'resource_assignment.resource_id']],
                ['ResourceDemand AS resource_demand',         true, null, ['resource_assignment.resource_demand_id', '=', 'resource_demand.id']],
            ],
            'where' => [
                ['resource_demand.id', '=', $this->id],
                ['resource_assignment.status', 'IN', $assignment_status],
            ],
        ]);

        // convert the result into a BAO list
        $results = [];
        foreach ($resource_list['values'] as $resource) {
            // todo: can we fetch them in one go?
            $bao = new CRM_Resource_BAO_Resource();
            $bao->id = $resource['id'];
            $bao->find(true);
            $results[] = $bao;
        }
        return $results;
    }

    /**
     * Get the list of demand conditions
     */
    public function getDemandConditions($cached = true)
    {
        static $demand_conditions = null;
        if ($demand_conditions === null || !$cached) {
            $demand_conditions = [];
            $condition_bao = new CRM_Resource_BAO_ResourceDemandCondition();
            $condition_bao->resource_demand_id = $this->id;
            $condition_bao->find();
            while ($condition_bao->fetch()) {
                $demand_conditions[] = $condition_bao->getImplementation();
            }
        }
        return $demand_conditions;
    }
}
