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

class CRM_Resource_BAO_ResourceUnavailability extends CRM_Resource_DAO_ResourceUnavailability
{
    /** @var CRM_Resource_BAO_ResourceUnavailability */
    private $implementation = null;

    /** @var array */
    private $json_parameters = null;

    /**
     * Create a new ResourceUnavailability based on array-data
     *
     * @param array $params key-value pairs
     *
     * @return CRM_Resource_DAO_ResourceUnavailability|NULL
     */
    public static function create($params)
    {
        $className = 'CRM_Resource_BAO_ResourceUnavailability';
        $entityName = 'ResourceUnavailability';
        $hook = empty($params['id']) ? 'create' : 'edit';

        CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
        $instance = new $className();
        $instance->copyValues($params);
        $instance->save();
        CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);
        return $instance;
    }

    /**
     * Check whether the given resource is available (in the given time frame)
     *
     * @todo move to resource
     *
     * @param integer $resource_id
     * @param string $from_timestamp
     * @param string $to_timestamp
     *
     * @return bool true iff available wrt the given time frame
     */
    public static function isResourceAvailable($resource_id, $from_timestamp = null, $to_timestamp = null) : bool
    {
        $resource = CRM_Resource_BAO_Resource::getInstance($resource_id);
        $unavailabilities = $resource->getUnavailabilities();
        foreach ($unavailabilities as $unavailability) {
            if ($unavailability->isActive($from_timestamp, $to_timestamp)) {
                return false;
            }
        }

        // check for currently assignments
        $assignment_search = new CRM_Resource_BAO_ResourceAssignment();
        $assignment_search->resource_id = (int)$resource_id;
        $assignment_search->status = CRM_Resource_BAO_ResourceAssignment::STATUS_CONFIRMED;
        $assignment_search->find();

        while ($assignment_search->fetch()) {
            if ($from_timestamp == null && $to_timestamp == null) {
                // in this case *any* assignment would render it unavailable
                return false;
            }

            // check if this assignment is valid during the given time frame
            // todo: implement!
            $implementation = $assignment_search->getImplementation();
            if ($implementation->isActive($from_timestamp, $to_timestamp)) {
                return false;
            }
        }

        // no problems found
        return true;
    }

    /**
     * Return an object of the specific class, i.e. the object that matches
     *   the provided class
     *
     * @return CRM_Resource_BAO_ResourceUnavailability subclass
     */
    public function getImplementation($cached = true) : object
    {
        if (!isset($this->implementation) || !$cached) {
            $this->implementation = new $this->class_name();
            $this->implementation->setFrom($this);
            $this->implementation->id = $this->id;
        }
        return $this->implementation;
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
     * Check if the
     *
     * @param null $from_timestamp
     * @param null $to_timestamp
     *
     * @return false
     */
    public function isActive($from_timestamp = null, $to_timestamp = null)
    {
        // this should really be overwritten
        Civi::log()->warning("CRM_Resource_BAO_ResourceUnavailability::isActive called, this should be overwritten");
        return true;
    }

    /**
     * Get a list of all classes implementing Unavailabilities
     *
     * @return array
     *   list of CRM_Resource_BAO_ResourceUnavailability subclasses
     */
    public static function getAllUnavailabilityTypes() : array
    {
        // todo: implement using Symfony event
        return [
            'CRM_Resource_Unavailability_Absolute',
            'CRM_Resource_Unavailability_DateRange',
//            'CRM_Resource_Unavailability_DateRepeat',
//            'CRM_Resource_Unavailability_Holidays',
        ];
    }

    /**
     * Get the proper label for this unavailability
     *
     * @return string
     *    the label of this unavailability type
     */
    public static function getTypeLabel()
    {
        return __CLASS__;
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

