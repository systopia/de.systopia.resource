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
 * Small toolkit around timeframes
 *
 * timeframes are list of 2-int-tuples [from, to] as given by strtotime
 */
class CRM_Resource_Timeframes
{
    /** @var array list of [from, to] tuples */
    protected $timeframes = [];

    /** @var boolean is this list consolidated? */
    protected $consolidated = false;

    /**
     * Create a new timeframes list
     *
     * @param array $timeframes
     */
    public function __construct($timeframes = [])
    {
        $this->timeframes = $timeframes;
        $this->consolidated = empty($timeframes); // an empty array is always consolidated :)
    }

    /**
     * Get the timeframes
     *
     * @param bool $consolidated
     *   consolidated means sorted, and in ascending order
     *
     * @return array
     *   the timeframe tuples
     */
    public function getTimeframes($consolidated = true)
    {
        if ($consolidated && !$this->consolidated) {
            // consolidate first
            $this->consolidateTimeframes();
        }
        return $this->timeframes;
    }

    /**
     * Check whether this set of timeframes is empty
     *
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->timeframes);
    }

    /**
     * Check whether this timeframe set overlaps with the given one.
     *
     * @param CRM_Resource_Timeframes $timeframes
     *
     * @return boolean
     *   true if the two timeframes
     */
    public function overlapsWith($timeframes)
    {
        return self::overlaps($this->getTimeframes(), $timeframes->getTimeframes(), true);
    }

    /**
     * Add a new timeframe
     *
     * @param integer $from
     * @param integer $to
     */
    public function addTimeframe($from, $to)
    {
        $this->consolidated = false;
        if ($from <= $to) {
            $this->timeframes[] = [$from, $to];
        } else {
            // this is not the idea, but ok...
            $this->timeframes[] = [$to, $from];
        }
    }

    /**
     * Add a list of timeframes
     *
     * @param array $time_frames
     */
    public function addTimeframes($time_frames)
    {
        $this->consolidated = false;
        $this->timeframes = array_merge($this->timeframes, $time_frames);
    }

    /**
     * Add another timeframes set
     *
     * @param CRM_Resource_Timeframes $time_frames
     */
    public function joinTimeframes($time_frames)
    {
        $this->addTimeframes($time_frames->getTimeframes(false));
    }

    /**
     * make the tuples sorted in ascending order, and join overlapping entries
     */
    public function consolidateTimeframes()
    {
        if (!$this->consolidated) {
            $this->timeframes = self::consolidate($this->timeframes);
            $this->consolidated = true;
        }
    }

    /**
     * Algorithm to consolidate overlapping timeframes
     *
     * @param array $all_time_frames
     *   list of 2-int-tuples [from, to] as given by strtotime
     *
     * @param boolean $merge_adjacent
     *   set to true, if you want to merge adjacent time frames. default is YES
     *
     * @return array
     *   list of 2-int-tuples [from, to] as given by strtotime
     */
    public static function consolidate($all_time_frames, $merge_adjacent = true)
    {
        $merged_time_frames = [];

        // then: sort by start time
        usort($all_time_frames, function ($a, $b) {
            return $a[0] <=> $b[0];
        });

        // then consolidate, i.e. join overlapping time frames
        if (!empty($all_time_frames)) {
            // start with the first one
            $current_time_frame = array_shift($all_time_frames);
            while (!empty($all_time_frames)) {
                $next_time_frame = array_shift($all_time_frames);
                if ($merge_adjacent) {
                    // merge adjacent frames (>=)
                    if ($current_time_frame[1] >= $next_time_frame[0]) {
                        // there is an overlap
                        $current_time_frame[1] = max($current_time_frame[1], $next_time_frame[1]);
                        $next_time_frame = null;
                    } else {
                        // there is no overlap, store the old one, and move on
                        $merged_time_frames[] = $current_time_frame;
                        $current_time_frame = $next_time_frame;
                    }
                } else {
                    // don't merge adjacent frames (>)
                    if ($current_time_frame[1] > $next_time_frame[0]) {
                        // there is an overlap
                        $current_time_frame[1] = max($current_time_frame[1], $next_time_frame[1]);
                        $next_time_frame = null;
                    } else {
                        // there is no overlap, store the old one, and move on
                        $merged_time_frames[] = $current_time_frame;
                        $current_time_frame = $next_time_frame;
                    }
                }
            }
            if (isset($next_time_frame)) {
                $merged_time_frames[] = $next_time_frame;
            } else {
                $merged_time_frames[] = $current_time_frame;
            }
        }

        return $merged_time_frames;
    }

    /**
     * Algorithm check whether two timeframe sets overlap/collide
     *
     * @param array $time_frames_1
     *   list of 2-int-tuples [from, to] as given by strtotime
     *
     * @param array $time_frames_2
     *   list of 2-int-tuples [from, to] as given by strtotime
     *
     * @param boolean $is_consolidated
     *   the give time frame sets are already consolidated
     *
     * @return boolean
     *   true if they overlap
     */
    public static function overlaps($time_frames_1, array $time_frames_2)
    {
        // lazy algorithm
        // @todo: implement something smarter and faster?
        $all_entries = array_merge($time_frames_1, $time_frames_2);
        $consolidated_entries = self::consolidate($all_entries, false);
        return count($all_entries) > $consolidated_entries;
    }
}