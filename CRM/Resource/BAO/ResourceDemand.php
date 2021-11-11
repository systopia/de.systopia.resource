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
     * Get the resource BAO
     *
     * @param integer $id
     *   the resource ID
     *
     * @return CRM_Resource_BAO_ResourceDemand
     *   the resource
     */
    public static function getInstance($id, $cached = true)
    {
        $id = (int) $id;
        static $resource_demands = [];
        if (!isset($resource_demands[$id]) || !$cached) {
            $resource_demand = new CRM_Resource_BAO_ResourceDemand();
            $resource_demand->id = $id;
            if (!$resource_demand->find(true)) {
                throw new CRM_Core_Exception("CRM_Resource_BAO_ResourceDemand [{$id}] not found.");
            }
            $resource_demands[$id] = $resource_demand;
        }
        return $resource_demands[$id];
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
        // check if the general conditions are met
        $demand_conditions = $this->getDemandConditions($cached);
        foreach ($demand_conditions as $demand_condition) {
            /** @var $demand_condition CRM_Resource_BAO_ResourceDemandCondition */
            $error_message = null;
            if (!$demand_condition->isFulfilledWithResource($resource, $error_message)) {
                return false;
            }
        }

        // also check if the resources are available at relevant times
        $demand_timeframes = $this->getResourcesBlockedTimeframes();
        if ($demand_timeframes->isEmpty()) {
            // no blocked time frames means resource is ALWAYS blocked.
            // In this case it's only true, if it's
            //   not assigned yet OR already exclusively assigned to this demand
            $other_assignment_count = civicrm_api4('ResourceAssignment', 'get', [
                'select' => ['row_count',],
                'where' => [
                    ['resource_id', '=', $resource->id],
                    ['resource_demand_id', '!=', $this->id],
                    ['status', '=', CRM_Resource_BAO_ResourceAssignment::STATUS_CONFIRMED],
                ],
                'limit' => 1,
            ]);
            return $other_assignment_count->rowCount <= 0;

        } else {
            // check if the resource is available for all timeframes
            foreach ($demand_timeframes as $demand_timeframe) {
                if (!$resource->isAvailable($demand_timeframe[0], $demand_timeframe[1])) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Get a number of candidates potentially matching this resource demand
     *
     * @param $count integer maximal number of resources
     *
     * @return array list of CRM_Resource_BAO_Resource
     */
    public function getResourceCandidates($count)
    {
        // run a query to simply find resources matching the type, and then iterate and test
        // todo: optimise, this is a quick hack
        // fixme: this should eventually be replaced by an elaborate, dynamically generated sql query
        $resource_candidates = [];

        // get already assigned resources
        $assigned_resources = $this->getAssignedResources();

        // build resource search query
        $candidate_query = new CRM_Resource_BAO_Resource();
        $candidate_query->resource_type_id = $this->resource_type_id;
        $candidate_query->_query['order_by'] = "ORDER BY RAND()";
        $candidate_query->find();

        while ($candidate_query->fetch()) {
            if ($this->isFulfilledWithResource($candidate_query)) {
                // check if it's already assigned
                if (isset($assigned_resources[$candidate_query->id])) {
                    continue; // already assigned
                }
                $candidate = new CRM_Resource_BAO_Resource();
                $candidate->setFrom($candidate_query);
                $candidate->id = $candidate_query->id;
                $resource_candidates[] = $candidate;
            }
            if (count($resource_candidates) >= $count) {
                break;
            }
        }
        $candidate_query->free();
        return $resource_candidates;
    }

    /**
     * Get the number of assignments to this resource demand
     *
     * @param array|integer $status
     *   the status(es) to consider
     *
     * @return integer
     *   number of references
     */
    public function getAssignmentCount($status = CRM_Resource_BAO_ResourceAssignment::STATUS_CONFIRMED)
    {
        // prepare status list
        if (!is_array($status)) {
            $status = [$status];
        }
        $status = array_map('intval', $status);
        $status_list = implode(',', $status);

        return CRM_Core_DAO::singleValueQuery("
            SELECT COUNT(id) 
            FROM civicrm_resource_assignment
            WHERE resource_demand_id = %1
              AND status IN (%2)", [
            1 => [$this->id, 'Positive'],
            2 => [$status_list, 'CommaSeparatedIntegers']
        ]);
    }

    /**
     * Get the number of conditions attached to this resource demand
     *
     * @return integer
     *   number of conditions
     */
    public function getConditionCount()
    {
        return (int) CRM_Core_DAO::singleValueQuery("
            SELECT COUNT(id) 
            FROM civicrm_resource_demand_condition
            WHERE resource_demand_id = %1", [
            1 => [$this->id, 'Positive']
        ]);
    }

    /**
     * Get the number of assigned resources that fulfill the conditions
     *
     * @return integer
     *   number of conditions
     */
    public function getFulfilledCount()
    {
        $fulfilled_count = 0;
        foreach ($this->getAssignedResources() as $assignedResource) {
            if ($this->isFulfilledWithResource($assignedResource)) {
                $fulfilled_count++;
            }
        }
        return $fulfilled_count;
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
     * Get a list of from-to time markers during which the
     *   assigned resources are considered to be blocked for other use
     *
     * If this list is empty, it should be considered to be
     *   blocked indefinitely
     *
     * @return \CRM_Resource_Timeframes
     *   list of 2-int-tuples [from, to] as given by strtotime
     */
    public function getResourcesBlockedTimeframes()
    {
        // first: collect all time frames
        $timeframes = new CRM_Resource_Timeframes();
        foreach ($this->getDemandConditions() as $demand_condition) {
            /** @var CRM_Resource_BAO_ResourceDemandCondition $demand_condition */
            $timeframes->joinTimeframes($demand_condition->getResourcesBlockedTimeframes());
        }

        return $timeframes;
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
        $assignment_status_list = implode(',', $assignment_status);

        // use a sql query, apiv4 somehow didn't work - see below
        $query = CRM_Core_DAO::executeQuery("
            SELECT assignment.resource_id AS resource_id
            FROM civicrm_resource_assignment assignment
            WHERE assignment.resource_demand_id = %1
              AND assignment.status IN (%2);",
            [
                1 => [$this->id, 'Integer'],
                2 => [$assignment_status_list,  'CommaSeparatedIntegers']
            ]
        );

        $results = [];
        foreach ($query->fetchAll() as $resource) {
            // todo: can we fetch them in one go?
            $bao = new CRM_Resource_BAO_Resource();
            $bao->id = $resource['resource_id'];
            $bao->find(true);
            $results[$bao->id] = $bao;
        }


        /* todo: this doesn't work - fix it?
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
        }*/

        return $results;
    }

    /**
     * Get the list of demand conditions
     */
    public function getDemandConditions($cached = true)
    {
        static $demand_conditions = [];
        if (!isset($demand_conditions[$this->id]) || !$cached) {
            $demand_conditions[$this->id] = [];
            $condition_bao = new CRM_Resource_BAO_ResourceDemandCondition();
            $condition_bao->resource_demand_id = $this->id;
            $condition_bao->find();
            while ($condition_bao->fetch()) {
                $demand_conditions[$this->id][] = $condition_bao->getImplementation();
            }
        }
        return $demand_conditions[$this->id];
    }

    /**
     * Try to get a label of the linked entity
     *
     * @return string a label
     */
    public function getEntityLabel()
    {
        // todo:: symfony hooks?
        $class_name = CRM_Core_DAO_AllCoreTables::getClassForTable($this->entity_table);
        /** @var CRM_Core_DAO $dao */
        $dao = new $class_name();
        $dao->id = $this->entity_id;
        $dao->find(true);

        // try to find a label/name/title
        // @todo CRM_Core_DAO has _labelField but doesn't seem to be accessible
        foreach (['name', 'display_name', 'title', 'label', 'subject'] as $property) {
            if (isset($dao->$property)) {
                return $dao->$property;
            }
        }

        // no name found in the property
        return CRM_Core_DAO_AllCoreTables::getEntityNameForTable($this->entity_table);
    }

    /**
     * Get a rendered version of the blocked timeframe
     *
     * @return string
     */
    public function getRenderedTimeframe()
    {
        $timeframes = $this->getResourcesBlockedTimeframes()->getTimeframes(true);
        if (empty($timeframes)) {
            return E::ts("forever (no timespan)");
        } else {
            // todo: implement a more human-readable version
            return date("Y-m-d H:i\h", $timeframes[0][0]) . ' - ' .  date("H:i\h", $timeframes[-1][1]);
        }
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
