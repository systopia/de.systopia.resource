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
class CRM_Resource_DemandCondition_Tagged extends CRM_Resource_BAO_ResourceDemandCondition
{
    /**
     * Get the proper label for this unavailability
     *
     * @return string
     *    the label of this unavailability type
     */
    public static function getTypeLabel()
    {
        return E::ts("Tags");
    }

    /**
     * Get a font-awesome icon for this condition
     */
    public function getIcon()
    {
        return 'fa-tag';
    }

    /**
     * Create a new AttributeResourceDemandCondition
     *
     * @param integer $resource_demand_id
     *   resource demand ID
     *
     * @param integer $tag_id
     *   the ID of the tag
     *
     * @return \CRM_Resource_BAO_ResourceDemandCondition
     */
    public static function createCondition(string $resource_demand_id, int $tag_id): CRM_Resource_DemandCondition_Attribute
    {
        $params = [
            'resource_demand_id' => $resource_demand_id,
            'class_name'  => 'CRM_Resource_DemandCondition_Tagged',
        ];

        // "pack" the parameters
        $params['parameters'] = [$tag_id];

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
        list($tag_id) = $this->getParametersParsed();
        $tag_id = (int) $tag_id;
        $demand = $this->getResourceDemand();

        $matched = CRM_Core_DAO::singleValueQuery("
            SELECT COUNT(*) 
            FROM civicrm_entity_tag
            WHERE tag_id = %1
              AND entity_id = %2
              AND entity_table = %3", [
            1 => [$tag_id, 'Integer'],
            2 => [$demand->entity_id, 'Integer'],
            3 => [$demand->entity_table, 'String'],
        ]);

        return $matched > 0;
    }

    /**
     * Get the proper label for this unavailability
     */
    public function getLabel()
    {
        list($tag_id) = $this->getParametersParsed();
        $tags = \Civi\Api4\Tag::get()
            ->addSelect('name')
            ->addWhere('id', '=', (int) $tag_id)
            ->execute();

        return E::ts("Resource is tagged as \"%1\"",
            [
                1 => $tags->first()['name']
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
        // get tags
        $tag_list = [];
        $tags = \Civi\Api4\Tag::get()->addSelect('name', 'id')->execute();
        foreach ($tags as $tag) {
            // do something
            $tag_list[$tag['id']] = $tag['name'];
        }

        $form->add(
            'select',
            $prefix . '_tag_id',
            E::ts("Tag"),
            $tag_list,
            true,
            ['class' => 'crm-select2']
        );

        return [
            $prefix . '_tag_id',
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
            $data["{$prefix}_tag_id"],
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

        if (isset($params[0])) {
            return [
                "{$prefix}_tag_id" => $params[0],
            ];
        } else {
            return [];
        }
    }
}
