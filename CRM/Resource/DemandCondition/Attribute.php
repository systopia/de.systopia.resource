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
 * This resource demand tests for exactly one attribute of the target resource.
 *
 * The implementation is based on APIv4 interface
 */
class CRM_Resource_DemandCondition_Attribute extends CRM_Resource_BAO_ResourceDemandCondition
{
    /**
     * Get the proper label for this unavailability
     *
     * @return string
     *    the label of this unavailability type
     */
    public static function getTypeLabel()
    {
        return E::ts("Attribute Value");
    }

    /**
     * Get an font-awesome icon for this condition
     */
    public function getIcon()
    {
        return 'fa-question';
    }

    /**
     * Get the entity name in the APIv4 linked to the resource demand
     *
     * @param CRM_Resource_BAO_ResourceDemand $resource_demand
     *   the resource demand
     * 
     * @return string entity name
     */
    public static function getApi4Entity($resource_demand)
    {
        // todo: adjustments needed?
        $resource_type = CRM_Resource_Types::getType($resource_demand->resource_type_id);
        return CRM_Core_DAO_AllCoreTables::getEntityNameForTable($resource_type['entity_table']);
    }

    /**
     * Create a new AttributeResourceDemandCondition
     *
     * @param integer $resource_demand_id
     *   resource demand ID
     *
     * @param string $attribute_name
     *   the name of the attribute to check
     *
     * @param mixed $value
     *   the value to check for
     *
     * @param string $operation
     *   the operation to execute
     *
     * @return \CRM_Resource_BAO_ResourceDemandCondition
     */
    public static function createCondition(string $resource_demand_id, string $attribute_name, string $value, string $operation = '=='): CRM_Resource_DemandCondition_Attribute
    {
        $params = [
            'resource_demand_id' => $resource_demand_id,
            'class_name'  => 'CRM_Resource_DemandCondition_Attribute',
        ];

        // "pack" the parameters
        $params['parameters'] = json_encode([$attribute_name, $value, $operation]);

        // we're good, run the creation
        /** @var CRM_Resource_BAO_ResourceDemandCondition $condition_bao */
        $condition_bao = CRM_Resource_BAO_ResourceDemandCondition::create($params);
        return $condition_bao->getImplementation();
    }

    /**
     * Check if the given condition is currently met
     *
     * @param \CRM_Resource_BAO_Resource $resource
     *    the resource to be tested against
     *
     * @param array $error_messages
     *    if there is a problem, add a description to the list of error messages
     *
     * @return boolean does the resource fulfill this condition
     *
     * @note this should be overwritten by the subclass implementation
     */
    public function isFulfilledWithResource($resource, &$error_messages = []) : bool
    {
        $params = $this->getParametersParsed();
        if (empty($params) || count($params) < 3) {
            Civi::log()->warning("Garbled parameters for condition [{$this->id}]");
            return false;
        }
        $entity_name = self::getApi4Entity($this->getResourceDemand());
        $attribute_name = $params[0];
        $attribute_value = $params[1];
        $attribute_operation = $params[2];

        $resources = \Civi\Api4\Resource::get()
            ->setJoin([["{$entity_name} AS entity", TRUE, NULL, ['entity_id', '=', 'entity.id']]])
            ->addWhere("entity.{$attribute_name}", $attribute_operation, $attribute_value)
            ->addWhere("id", '=', $resource->id)
            ->setLimit(1)
            ->execute();
        $count = $resources->count();
        return $count > 0;
    }

    /**
     * Get the proper label for this unavailability
     */
    public function getLabel()
    {
        // todo: improve
        $params = $this->getParametersParsed();

        $entity_name = self::getApi4Entity($this->getResourceDemand());
        $field_specs = civicrm_api4($entity_name, 'getFields', [
            'where' => [['name', '=', $params[0]]]
        ]);
        $field_spec = $field_specs->first();

        return E::ts("Attribute \"%1\" <code>%2</code> \"%3\"",
            [
                1 => $field_spec['label'],
                2 => $params[2],
                3 => trim(json_encode($params[1]), '"'),
            ]
        );
    }

    /*****************************************
     ***          FORM INTEGRATION          **
    /****************************************/

    /**
     * Add form fields for the given unavailability
     *
     * @param $form CRM_Core_Form
     *   a form the parameters should be added to
     *
     * @param $prefix string
     *   the prefix to be used to make sure there is no clash in forms
     *
     * @param $demand_bao CRM_Resource_BAO_ResourceDemand
     *   the resource demand this condition belongs to
     *
     * @return array
     *    list of field keys (incl. prefix)
     */
    public static function addFormFields($form, $prefix = '', $demand_bao = null)
    {
        // extract field names
        $field_names = [];
        $entity_name = self::getApi4Entity($demand_bao);
        $field_specs = civicrm_api4($entity_name, 'getFields');
        foreach ($field_specs->getIterator() as $field_spec) {
            $field_names[$field_spec['name']] = $field_spec['title'];
        }

        // todo: pull? label? translate?
        $operators = [
            '=' => '=',
            '<=' => '<=',
            '>=' => '>=',
            '>' => '>',
            '<' => '<',
            'LIKE' => 'LIKE',
            '<>' => '<>',
            'NOT LIKE' => 'NOT LIKE',
            'IN' => 'IN',
            'NOT IN' => 'NOT IN',
            'IS NULL' => 'IS NULL',
            'IS NOT NULL' => 'IS NOT NULL',
            'CONTAINS' => 'CONTAINS',
        ];

        $form->add(
            'select',
            $prefix . '_field_name',
            E::ts("Field"),
            $field_names,
            true,
            ['class' => 'crm-select2']
        );

        $form->add(
            'select',
            $prefix . '_operator',
            E::ts("Operator"),
            $operators,
            true,
            ['class' => 'crm-select2']
        );

        // add date
        $form->add(
            'text',
            $prefix . '_value',
            E::ts("Value"),
            NULL,
            FALSE,
            []);

        return [
            $prefix . '_field_name',
            $prefix . '_operator',
            $prefix . '_value',
        ];
    }

    /**
     * Validate our values in the form submission
     *
     * @param $submit_values array
     *   the submitted values
     *
     * @return array
     *    validation errors [field_name => error]
     */
    public static function validateFormSubmission($submit_values, $prefix = '')
    {
        // todo: does there have to be any validation
        return [];
    }

    /**
     * Generate data values
     *
     * @param $data array
     *   form data
     *
     * @param $prefix string
     *   the prefix to be used to make sure there is no clash in forms
     *
     * @return array
     *   the data that should be written into the parameters field as a json blob
     */
    public static function compileParameters($data, $prefix = '')
    {
        // format the from/to values properly
        return [
            $data["{$prefix}_field_name"],
            $data["{$prefix}_value"],
            $data["{$prefix}_operator"],
        ];
    }

    /**
     * Get the current values for the fields defined in ::addFormFields
     *
     * @param string $prefix
     *   an optional prefix
     *
     * @return array
     *   field-key => current value
     */
    public function getCurrentFormValues($prefix = '')
    {
        $params = $this->getParametersParsed();

        if (isset($params[2])) {
            return [
                "{$prefix}_field_name" => $params[0],
                "{$prefix}_value"      => $params[1],
                "{$prefix}_operator"   => $params[2],
            ];
        } else {
            return [];
        }
    }
}
