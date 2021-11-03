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
 * This unavailability simply says the resource is unavailable indefinitely
 */
class CRM_Resource_Unavailability_Absolute extends CRM_Resource_BAO_ResourceUnavailability
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
        return true;
    }

    /**
     * Get the label of the resource unavailability
     *
     * @return string
     *   a localised, human-readable label of the unavailability
     */
    public function getLabel()
    {
        return E::ts("Generally Unavailable: %1", [1 => $this->reason]);
    }

    /**
     * Get the proper label for this unavailability
     *
     * @return string
     *    the label of this unavailability type
     */
    public static function getTypeLabel()
    {
        return E::ts("General Unavailability");
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
