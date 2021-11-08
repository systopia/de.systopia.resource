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

class CRM_Resource_BAO_ResourceDemandCondition extends CRM_Resource_DAO_ResourceDemandCondition
{
    /**
     * Get a list of all condition types (class name)
     *
     * @param string $entity_table
     *   can be added to filter the conditions to the given entity_table
     */
    public static function getAllConditionTypes($entity_table = null)
    {
        // todo: expose as Symfony hook
        $types = [
            'CRM_Resource_DemandCondition_Attribute' => [],
            'CRM_Resource_DemandCondition_EventTime' => ['civicrm_event'],
        ];
    }

    /**
     * Return an object of the specific class, i.e. the object that matches
     *   the provided class
     *
     * @return CRM_Resource_BAO_ResourceDemandCondition subclass
     */
    public function getImplementation()
    {
        $implementation = new $this->class_name();
        $implementation->setFrom($this);
        return $implementation;
    }

    /**
     * Create a new ResourceDemandCondition based on array-data
     *
     * @param array $params key-value pairs
     *
     * @return CRM_Resource_BAO_ResourceDemandCondition|NULL
     */
    public static function create($params)
    {
        // run pre hook
        $className = 'CRM_Resource_BAO_ResourceDemandCondition';
        $entityName = 'ResourceDemandCondition';
        $hook = empty($params['id']) ? 'create' : 'edit';
        CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);

        // create entity
        $instance = new $className();
        $instance->copyValues($params);
        $instance->save();

        // run post hook
        CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);
        return $instance;
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
        $error_messages[] = "this is the abstract function and should never be called";
        return false;
    }

    /**
     * Get a list of from-to time markers during which the
     *   assigned resources are considered to be blocked for other use
     *
     * If this list is empty, it should be considered to be
     *   blocked indefinitely
     *
     * @return CRM_Resource_Timeframes
     *   timeframes list
     *
     * @note this should be overwritten by the subclass implementation
     */
    public function getResourcesBlockedTimeframes()
    {
        return new CRM_Resource_Timeframes();
    }

    /**
     * Get a parsed version of the stored parameters
     *
     * @return array|null
     */
    public function getParameters()
    {
        return json_decode($this->parameters);
    }

    /**
     * Get the parsed version of the parameter column
     *
     * @return array parameters
     */
    public function getParametersParsed() : array
    {
        if ($this->json_parameters === null) {
            $this->json_parameters = json_decode($this->parameters);
            if ($this->json_parameters === null) {
                $this->json_parameters = [];
            }
        }
        return $this->json_parameters;
    }

    /**
     * Get the proper label for this unavailability
     */
    public function getLabel()
    {
        return 'NOT IMPLEMENTED';
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
     * @return array
     *    list of field keys (incl. prefix)
     */
    public static function addFormFields($form, $prefix = '')
    {
        // some subclasses don't have to implement this, so no warning here
        return [];
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
    public static function validateFormSubmission($submit_values)
    {
        // some subclasses don't have to implement this, so no warning here
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
        // some subclasses don't have to implement this, so no warning here
        return [];
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
        return [];
    }
}
