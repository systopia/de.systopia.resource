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
     * Get the linked entity
     *
     * @return CRM_Core_DAO the linked entity
     */
    public function getEntity($cached = true)
    {
        if (empty($this->entity) || !$cached) {
            $this->entity = null;
            $entity_class = CRM_Core_DAO_AllCoreTables::getClassForTable($this->entity_table);
            /** @var CRM_Core_DAO $entity */
            $entity = new $entity_class();
            $entity->id = $this->entity_id;
            if ($entity->find(true)) {
                $this->entity = $entity;
            } else {
                throw new Exception("Entity linked to resource [{$this->id}] does not exist.");
            }
        }
        return $this->entity;
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
    public function isAvailable($from_timestamp = null, $to_timestamp = null)
    {
        return CRM_Resource_BAO_ResourceUnavailability::isResourceAvailable($this->id, $from_timestamp, $to_timestamp);
    }
}
