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
class CRM_Resource_DemandCondition_EventTime extends CRM_Resource_BAO_ResourceDemandCondition
{
    /**
     * Get the proper label for this unavailability
     *
     * @return string
     *    the label of this unavailability type
     */
    public static function getTypeLabel()
    {
        return E::ts("Availability (Event)");
    }

    /**
     * Create a new EventTime Condition
     *
     * @param integer $resource_demand_id
     *   resource demand ID
     *
     * @param string $buffer_before
     *   amount of minutes the resource should be blocked before the event officially starts
     *
     * @param string $buffer_after
     *   amount of minutes the resource should be blocked after the event officially ends
     *
     *
     * @return \CRM_Resource_BAO_ResourceDemandCondition
     */
    public static function createCondition(string $resource_demand_id, $buffer_before = 0, $buffer_before_unit = 'hours', $buffer_after = 0, $buffer_after_unit = 'hours'): CRM_Resource_DemandCondition_EventTime
    {
        $params = [
            'resource_demand_id' => $resource_demand_id,
            'class_name'  => 'CRM_Resource_DemandCondition_EventTime',
        ];

        // "pack" the parameters
        $params['parameters'] = json_encode([[$buffer_before, $buffer_before_unit], [$buffer_after, $buffer_after_unit]]);

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

        // get event start_date/end_date
        $event_query = civicrm_api4('ResourceDemand', 'get', [
            'select' => ['event.start_date', 'event.end_date'],
            'join'   => [['Event AS event', TRUE, NULL, ['entity_id', '=', 'event.id']]],
            'where' =>  [['id', '=', $this->resource_demand_id]],
            'limit'  => 1,
        ]);
        $event = $event_query->getArrayCopy()[0];

        // calculate the time
        $params = $this->getParametersParsed();
        $event_start = $event['event.start_date'];
        $event_end = empty($event['event.end_date']) ?
            $event['event.start_date'] : $event['event.end_date'];

        // compile the timeframe
        $timeframe_from = strtotime("-{$params[0][0]} {$params[0][1]}", strtotime($event_start));
        $timeframe_to   = strtotime("+{$params[1][0]} {$params[1][1]}", strtotime($event_end));
        if ($timeframe_from > $timeframe_to) {
            Civi::log()->warning("DemandCondition_EventTime[{$this->id}] has overlapping timeframes");
            $timeframe_tmp = $timeframe_to;
            $timeframe_to = $timeframe_from;
            $timeframe_from = $timeframe_tmp;
        }
        $timeframes->addTimeframe($timeframe_from, $timeframe_to);

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
        $params = $this->getParametersParsed();
        $before_term = $this->getTimeTerm($params[0][0], $params[0][1], true);
        $after_term = $this->getTimeTerm($params[1][0], $params[1][1], false);
        return E::ts("Resource available from %1 until %2", [1 => $before_term, 2 => $after_term]);
    }

    /**
     * Render a time term
     *
     * @param integer $quantity
     *   number of time units, e.g.
     * @param string $unit
     *   time unit, e.g. hour
     * @param boolean $before
     *   true if the term should say *before*, otherwise after
     */
    public function getTimeTerm($quantity, $unit, $before)
    {
        if (empty($quantity)) {
            if ($before) {
                return E::ts("event start");
            } else {
                return E::ts("event end");
            }
        }

        if ($quantity > 0) {
            if ($before) {
                return E::ts("<b>%1 %2(s)</b> before event starts",
                    [1 => $quantity, 2 => $unit]);
            } else {
                return E::ts("<b>%1 %2(s)</b> after event ends",
                             [1 => $quantity, 2 => $unit]);
            }
        } else {
            if ($before) {
                return E::ts("<b>%1 %2(s)</b> after event starts",
                             [1 => $quantity, 2 => $unit]);
            } else {
                return E::ts("<b>%1 %2(s)</b> before event ends",
                             [1 => $quantity, 2 => $unit]);
            }
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
    public static function addFormFields($form, $prefix = '', $demand_bao = null)
    {
        $units = [
            'minute' => E::ts("minute(s)"),
            'hour'   => E::ts("hour(s)"),
            'day'    => E::ts("day(s)"),
            'week'   => E::ts("week(s)"),
        ];
        $form->add(
            'text',
            $prefix . '_before_quantity',
            E::ts("Before Event Start"),
            ['size' => '5'],
            true
        );
        $form->add(
            'select',
            $prefix . '_before_unit',
            '',
            $units,
            true,
            ['class' => 'crm-select2']
        );
        $form->add(
            'text',
            $prefix . '_after_quantity',
            E::ts("After Event End"),
            ['size' => '5'],
            true
        );
        $form->add(
            'select',
            $prefix . '_after_unit',
           '',
            $units,
            true,
            ['class' => 'crm-select2']
        );
        $form->addRule($prefix . '_before_quantity', E::ts("Please enter a number"), 'integer');
        $form->addRule($prefix . '_after_quantity', E::ts("Please enter a number"), 'integer');

        return [
            $prefix . '_before_quantity',
            $prefix . '_before_unit',
            $prefix . '_after_quantity',
            $prefix . '_after_unit',
        ];
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
        return [
            [$data[$prefix . '_before_quantity'], $data[$prefix . '_before_unit']],
            [$data[$prefix . '_after_quantity'], $data[$prefix . '_after_unit']]
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
        $values = [];
        $params = $this->getParametersParsed();
        if (!empty($params)) {
            $values[$prefix . '_before_quantity'] = $params[0][0];
            $values[$prefix . '_before_unit'] = $params[0][1];
            $values[$prefix . '_after_quantity'] = $params[1][0];
            $values[$prefix . '_after_unit'] = $params[1][1];
        }
        return $values;
    }
}
