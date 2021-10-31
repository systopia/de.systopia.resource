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
 * This unavailability defined by a date range
 */
class CRM_Resource_Unavailability_DateRange extends CRM_Resource_BAO_ResourceUnavailability
{
    /**
     * Check if the
     * @param null $from_timestamp
     * @param null $to_timestamp
     *
     * @return false
     */
    public function isActive($from_timestamp = null, $to_timestamp = null)
    {
        // this is always active
        list($unavailable_from, $unavailable_until) = $this->getParametersParsed();
        $unavailable_from = strtotime($unavailable_from);
        $unavailable_until = strtotime($unavailable_until);

        // check if the requested range overlaps with the unavailability
        if ($from_timestamp) {
            $request_from = strtotime($from_timestamp);
            if ($request_from >= $unavailable_from && $request_from <= $unavailable_until) {
                // the start time of the requested range is in the unavailability range
                return true;
            }
        }

        if ($to_timestamp) {
            $request_until = strtotime($to_timestamp);
            if ($request_until >= $unavailable_from && $request_until <= $unavailable_until) {
                // the start time of the requested range is in the unavailability range
                return true;
            }
        }

        return false;
    }

    /**
     * Create a new ResourceUnavailability based on array-data
     *
     * @param integer $resource_id
     *   resource ID
     *
     * @param string $reason
     *   the reason
     *
     * @param string $from_timestamp
     *   timestamp, must be parsable by strtotime
     *
     * @param string $to_timestamp
     *   timestamp, must be parsable by strtotime
     *
     *
     * @return CRM_Resource_DAO_ResourceUnavailability
     */
    public static function createUnavailability($resource_id, $reason, $from_timestamp = 'now', $to_timestamp = 'now')
    {
        $params = [
            'resource_id' => $resource_id,
            'reason'      => $reason,
            'class_name'  => 'CRM_Resource_Unavailability_DateRange',
        ];

        // add the timestamp data
        $from_timestamp = strtotime($from_timestamp);
        $to_timestamp = strtotime($to_timestamp);
        $params['parameters'] = json_encode([
            date('Y-m-d H:i:s e', $from_timestamp),
            date('Y-m-d H:i:s e', $to_timestamp),
        ]);

        // we're good, run the creation
        return parent::create($params);
    }

    /**
     * Get the label of the resource unavailability
     *
     * @return string
     *   a localised, human-readable label of the unavailability
     */
    public static function getLabel() : string
    {
        // all classes should implement this, so we shouldn't get here
        return E::ts("Date Range");
    }

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
        // no input fields needed
        $form->addDateRange($prefix . 'date_range');

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
        // no parameters needed
        return [];
    }
}
