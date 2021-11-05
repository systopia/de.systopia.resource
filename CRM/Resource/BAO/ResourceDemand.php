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
    public function isFulfilledWithResource($resource, $cached = true, &$error_list = [])
    {
        $demand_conditions = $this->getDemandConditions($cached);
        foreach ($demand_conditions as $demand_condition) {
            /** @var $demand_condition CRM_Resource_BAO_ResourceDemandCondition */
            $error_message = null;
            if (!$demand_condition->isFulfilledWithResource($resource, $error_message)) {
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
     * Algorithm to consolidate overlapping timeframes
     *
     * @param array $all_time_frames
     *   list of 2-int-tuples [from, to] as given by strtotime
     *
     * @return array
     *   list of 2-int-tuples [from, to] as given by strtotime
     */
    public static function consolidateTimeframes($all_time_frames)
    {
        $merged_time_frames = [];

        // then: sort by start time
        usort($all_time_frames, function($a, $b) {
            return $a[0] <=> $b[0];
        });

        // then consolidate, i.e. join overlapping time frames
        $merged_time_frames = [];
        if (!empty($all_time_frames)) {
            // start with the first one
            $current_time_frame = array_shift($merged_time_frames);
            while (!empty($merged_time_frames)) {
                $next_time_frame = array_shift($merged_time_frames);
                if ($current_time_frame[1] >= $next_time_frame[0]) {
                    // there is an overlap
                    $current_time_frame[1] = max($current_time_frame[1], $next_time_frame[1]);
                    $next_time_frame = null;
                } else {
                    // there is no overlap, store the old one, and move on
                    $merged_time_frames[] = $current_time_frame;
                    $current_time_frame = $next_time_frame;
                }
            }
            if (isset($next_time_frame)) {
                $merged_time_frames[] = $next_time_frame;
            }
        }

        return $merged_time_frames;
    }

    /**
     * Get a list of from-to time markers during which the
     *   assigned resources are considered to be blocked for other use
     *
     * If this list is empty, it should be considered to be
     *   blocked indefinitely
     *
     * @return array
     *   list of 2-int-tuples [from, to] as given by strtotime
     */
    public function getResourcesBlockedTimeframes()
    {
        // first: collect all time frames
        $all_time_frames = [];
        foreach ($this->getDemandConditions() as $demand_condition) {
            /** @var CRM_Resource_BAO_ResourceDemandCondition $demand_condition */
            $all_time_frames = array_merge($all_time_frames, $demand_condition->getResourcesBlockedTimeframes());
        }

        // consolidate and return
        return CRM_Resource_BAO_ResourceDemand::consolidateTimeframes($all_time_frames);
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
        if (isset($resource_list['values'])) {
            foreach ($resource_list['values'] as $resource) {
                // todo: can we fetch them in one go?
                $bao = new CRM_Resource_BAO_Resource();
                $bao->id = $resource['id'];
                $bao->find(true);
                $results[] = $bao;
            }
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

    /**
     * Get a list of BAOs of the resource demands on the specified entity
     *
     * @param integer $entity_id
     * @param string $entity_table
     */
    public static function getResourceDemandsFor($entity_id, $entity_table)
    {
        $resource_demands = [];
        $demand_search = new CRM_Resource_BAO_ResourceDemand();
        $demand_search->entity_id = $entity_id;
        $demand_search->entity_table = $entity_table;
        $demand_search->find();
        while ($demand_search->fetch()) {
            $demand_bao = new CRM_Resource_BAO_ResourceDemand();
            $demand_bao->setFrom($demand_search);
            $demand_bao->id = $demand_search->id;
            $resource_demands[] = $demand_bao;
        }
        return $resource_demands;
    }
}
