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

class CRM_Resource_BAO_Resource extends CRM_Resource_DAO_Resource
{
    /** @var CRM_Core_DAO entity */
    protected $entity = null;

    /**
     * Create a new Resource based on array-data
     *
     * @param array $params key-value pairs
     *
     * @return CRM_Resource_DAO_Resource|NULL
     */
    public static function create($params)
    {
        // pre hook
        $className = 'CRM_Resource_BAO_Resource';
        $entityName = 'Resource';
        $hook = empty($params['id']) ? 'create' : 'edit';
        CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);

        // create resource
        $instance = new $className();
        $instance->copyValues($params);
        $instance->save();

        // post hook
        CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);
        return $instance;
    }

    /**
     * Get the resource BAO
     *
     * @param integer $id
     *   the resource ID
     *
     * @return CRM_Resource_BAO_Resource
     *   the resource
     */
    public static function getInstance($id, $cached = true)
    {
        $id = (int) $id;
        static $resources = [];
        if (!isset($resources[$id]) || !$cached) {
            $resource = new CRM_Resource_BAO_Resource();
            $resource->id = $id;
            if (!$resource->find(true)) {
                throw new CRM_Core_Exception("CRM_Resource_BAO_Resource [{$id}] not found.");
            }
            $resources[$id] = $resource;
        }
        return $resources[$id];
    }

    /**
     * Get a number of candidates potentially matching this resource demand
     *
     * @param $count integer maximal number of resources
     *
     * @return array list of CRM_Resource_BAO_Resource
     */
    public function getDemandCandidates($count = null)
    {
        // run a query to simply find resources matching the type, and then iterate and test
        // todo: optimise, this is a quick hack
        // fixme: this should eventually be replaced by an elaborate, dynamically generated sql query
        $demand_candidates = [];

        // get already assigned resources
        $assignments = $this->getAssignedDemands();
        $assigned_demands = [];
        foreach ($assignments as $assignment) {
            $assigned_demands[$assignment->id] = true;
        }

        // build resource search query
        $resource_demand = new CRM_Resource_BAO_ResourceDemand();
        $resource_demand->resource_type_id = $this->resource_type_id;
        $resource_demand->_query['order_by'] = "ORDER BY entity_id DESC";
//        $resource_demand->_query['order_by'] = "ORDER BY RAND()";
        $resource_demand->find();

        while ($resource_demand->fetch()) {
            // check if it's already assigned
            if (isset($assigned_demands[$resource_demand->id])) {
                continue; // already assigned
            }
            if ($resource_demand->isFulfilledWithResource($this)) {
                $candidate = new CRM_Resource_BAO_ResourceDemand();
                $candidate->setFrom($resource_demand);
                $candidate->id = $resource_demand->id;
                $demand_candidates[] = $candidate;
                if ($count && count($demand_candidates) >= $count) {
                    break;
                }
            }
        }
        $resource_demand->free();
        return $demand_candidates;
    }


    /**
     * Get all the assigned resources
     *
     * @param int|array $assignment_status
     *    the status of the assignments to be considered
     *
     * @return array assignment_id => CRM_Resource_BAO_ResourceDemand
     */
    public function getAssignedDemands($assignment_status = [CRM_Resource_BAO_ResourceAssignment::STATUS_CONFIRMED])
    {
        if (!is_array($assignment_status)) {
            $assignment_status = [$assignment_status];
        }

        // make sure they're all integers
        $assignment_status = array_map('intval', $assignment_status);
        $assignment_status_list = implode(',', $assignment_status);

        // use a sql query, apiv4 somehow didn't work - see below
        $query = CRM_Core_DAO::executeQuery("
            SELECT 
                   assignment.resource_demand_id AS demand_id,
                   assignment.id                 AS assignment_id
            FROM civicrm_resource_assignment assignment
            WHERE assignment.resource_id = %1
              AND assignment.status IN (%2);",
            [
                1 => [$this->id, 'Integer'],
                2 => [$assignment_status_list,  'CommaSeparatedIntegers'],
            ]
        );

        $results = [];
        foreach ($query->fetchAll() as $resource_demands) {
            // todo: can we fetch them in one go?
            $bao = new CRM_Resource_BAO_ResourceDemand();
            $bao->id = $resource_demands['demand_id'];
            $bao->find(true);
            // store
            $results[$resource_demands['assignment_id']] = $bao;
        }

        return $results;
    }

    /**
     * Used as in the entityTypes hook als follows:
     *  'links_callback' => ['CRM_Resource_BAO_Resource::add_resource_links']
     *
     * @note current not active
     *
     * @param string $class
     * @param array $links
     */
    public static function add_resource_links($class, &$links)
    {
        $links[] = new CRM_Core_Reference_Dynamic(self::getTableName(), 'entity_id', null, 'id', 'entity_table');
    }

    /** TEMPLATE: COPY THIS TO CRM_Resource_DAO_Resource AFTER REBUILDING THE DAOs! */
    public static function buildOptions($fieldName, $context = null, $props = [])
    {
        if ($fieldName == 'entity_table') {
            return CRM_Resource_BAO_Resource::getLinkedEntities();
        } else {
            return parent::buildOptions($fieldName, $context, $props);
        }
    }

    /**
     * Get the linked entities
     *
     * This is currently based on the resource type specs,
     *  which is an option group with the group_name field containing the table name
     *
     * @return array
     *  table_name => entity_name
     *
     * @todo migrate to CRM_Resource_Types
     */
    public static function getLinkedEntities()
    {
        static $linked_entities = null;
        if ($linked_entities === null) {
            $linked_entities = [];
            // use SQL since the API doesn't expose the grouping fields
            $group_data = CRM_Core_DAO::executeQuery(
                "
                SELECT
                 ov.grouping AS entity_table
                FROM civicrm_option_value ov
                INNER JOIN civicrm_option_group og
                       ON ov.option_group_id = og.id
                       AND og.name = 'resource_types'
                "
            )->fetchAll();
            foreach ($group_data as $group_datum) {
                $linked_entities[$group_datum['entity_table']] =
                    CRM_Core_DAO_AllCoreTables::getEntityNameForTable($group_datum['entity_table']);
            }
        }
        return $linked_entities;
    }

    /**
     * Check if the resource is available, judging by
     *  - the attached availabilities
     *  - current assignments
     *
     * @param null $from_timestamp
     * @param null $to_timestamp
     *
     * @return boolean true iff the resource is available (wrt to the time parameters)
     * @see CRM_Resource_BAO_Resource::isAvailableSqlClause
     *
     */
    public function isAvailable($from_timestamp = null, $to_timestamp = null) : bool
    {
        // check unavailabilities
        $unavailabilities = $this->getUnavailabilities();
        foreach ($unavailabilities as $unavailability) {
            if ($unavailability->isActive($from_timestamp, $to_timestamp)) {
                return false;
            }
        }

        // gather the current assignments
        $assigned_timeframes = new CRM_Resource_Timeframes();
        $demands = $this->getAssignedDemands();
        /** @var \CRM_Resource_BAO_ResourceDemand $demand */
        foreach ($demands as $demand) {
            $demand_timeframes = $demand->getResourcesBlockedTimeframes();
            if ($demand_timeframes->isEmpty()) {
                // we're assigned to an eternal demand (one without temporal constrictions)
                return false;
            } else {
                $assigned_timeframes->joinTimeframes($demand_timeframes);
            }
        }

        // check whether the current assignments collide with the requested time
        $requested_timeframe = new CRM_Resource_Timeframes();
        $requested_timeframe->addTimeframe($from_timestamp, $to_timestamp);
        return !$assigned_timeframes->overlapsWith($requested_timeframe);
    }

    /**
     * Get a list of the attached unavailablities
     *
     * @return array of CRM_Resource_BAO_ResourceUnavailability instances
     */
    public function getUnavailabilities()
    {
        $unavailabilities = [];

        // find and load all unavailabilities
        $unavailability_search = new CRM_Resource_BAO_ResourceUnavailability();
        $unavailability_search->resource_id = (int) $this->id;
        $unavailability_search->find();
        while ($unavailability_search->fetch()) {
            $unavailabilities[] = $unavailability_search->getImplementation(false);
        }

        return $unavailabilities;
    }

    /**
     * Get the resource with the given parameters
     *
     * @param integer $entity_id
     *   the ID of the entity this the resource is attached to
     *
     * @param string $entity_table
     *   the table name of the entity this the resource is attached to
     *
     * @return CRM_Resource_BAO_Resource|null the resource if it exists
     */
    public static function getResource($entity_id, $entity_table)
    {
        // make sure this is sensible data
        if (empty($entity_id) || empty($entity_table)) {
            return null;
        }

        // find the resource
        $bao = new CRM_Resource_BAO_Resource();
        $bao->entity_table = $entity_table;
        $bao->entity_id = $entity_id;
        if ($bao->find(true)) {
            return $bao;
        } else {
            return null;
        }
    }
}
