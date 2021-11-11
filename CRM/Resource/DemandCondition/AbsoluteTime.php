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
 * This resource demand tests for exactly one attribute of the target resource
 */
class CRM_Resource_DemandCondition_AbsoluteTime extends CRM_Resource_BAO_ResourceDemandCondition
{
    /**
     * Get the proper label for this unavailability
     *
     * @return string
     *    the label of this unavailability type
     */
    public static function getTypeLabel()
    {
        return E::ts("Availability (fixed)");
    }

    /**
     * Create a new EventTime Condition
     *
     * @param integer $resource_demand_id
     *   resource demand ID
     *
     * @param integer $from_timestamp
     *   timestamp (as used by strtotime)
     *
     * @param integer $to_timestamp
     *   timestamp (as used by strtotime)
     *
     * @return \CRM_Resource_BAO_ResourceDemandCondition
     */
    public static function createCondition(string $resource_demand_id, $from_timestamp, $to_timestamp): CRM_Resource_DemandCondition_EventTime
    {
        $params = [
            'resource_demand_id' => $resource_demand_id,
            'class_name'  => 'CRM_Resource_DemandCondition_AbsoluteTime',
        ];

        // "pack" the parameters
        $params['parameters'] = json_encode([$from_timestamp, $to_timestamp]);

        // we're good, run the creation
        /** @var CRM_Resource_BAO_ResourceDemandCondition $condition_bao */
        $condition_bao = CRM_Resource_BAO_ResourceDemandCondition::create($params);
        return $condition_bao->getImplementation();
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
        $timeframes = new CRM_Resource_Timeframes();

        $params = $this->getParametersParsed();
        // todo: validate?
        $timeframes->addTimeframe($params[0], $params[1]);
        return $timeframes;
    }

    /**
     * Check if the given condition is currently met
     *
     * @note since this is only a time restriction, which will be checked separately,
     *    we can simply return true here
     */
    public function isFulfilledWithResource($resource, &$error_messages = []) : bool
    {
        // this is only a time restriction, which will be checked separately
        /* @see getResourcesBlockedTimeframes */
        return true;
    }

    /**
     * Get an font-awesome icon for this condition
     */
    public function getIcon()
    {
        return 'fa-clock-o';
    }

    /**
     * Get the proper label for this unavailability
     */
    public function getLabel()
    {
        // todo: localise times/dates
        $params = $this->getParametersParsed();
        $from_date = date("Y-m-d", $params[0]);
        $to_date = date("Y-m-d", $params[1]);
        if ($from_date == $to_date) {
            $from_time = date("H:i\h", $params[0]);
            $to_time = date("H:i\h", $params[1]);
            return E::ts("Resource required on %1 between <b>%2 and %3</b>", [1 => $from_date, 2 => $from_time, 3 => $to_time]);
        } else {
            $from_term = date("Y-m-d H:i:s", $params[0]);
            $until_term = date("Y-m-d H:i:s", $params[1]);
            return E::ts("Resource required between <b>%1 and %2</b>", [1 => $from_term, 2 => $until_term]);
        }
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
        // add date
        $form->add(
            'datepicker',
            $prefix . '_from',
            E::ts("From"),
            NULL,
            FALSE,
            []);

        $form->add(
            'datepicker',
            $prefix . '_to',
            E::ts("To"),
            NULL,
            FALSE,
            []);

        return [
            $prefix . '_from',
            $prefix . '_to',
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
        $validation_errors = [];
        if (empty($submit_values["{$prefix}_from"])) {
            $validation_errors["{$prefix}_from"] = E::ts("No start date given");
        }
        if (empty($submit_values["{$prefix}_to"])) {
            $validation_errors["{$prefix}_to"] = E::ts("No end date given");
        }
        if (strtotime($submit_values["{$prefix}_to"]) <= strtotime($submit_values["{$prefix}_from"])) {
            $validation_errors["{$prefix}_to"] = E::ts("This has be after the start date");
        }
        return $validation_errors;
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
            strtotime($data["{$prefix}_from"]),
            strtotime($data["{$prefix}_to"]),
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
        if (isset($params[1])) {
            return [
                "{$prefix}_from" => $params[0],
                "{$prefix}_to"   => $params[1],
            ];
        } else {
            return [];
        }
    }
}
