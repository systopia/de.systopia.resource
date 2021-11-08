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

use Civi\Test;
use Civi\Test\Api3TestTrait;
use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;

require_once 'ResourceTestBase.php';

/**
 * Test cases for the contained algorithms
 *
 * @group headless
 */
class AlgorithmsTest extends ResourceTestBase implements HeadlessInterface, HookInterface,
                                                                      TransactionalInterface
{
    /**
     * Test the consolidateTimeframes algorithm
     */
    public function testConsolidateTimeframes()
    {
        $now = strtotime('now');

        // test 1: nicely ordered and not overlapping
        $test1 = [
            [strtotime('+0 hours', $now), strtotime('+1 hour', $now)],
            [strtotime('+2 hour', $now),  strtotime('+3 hour', $now)],
            [strtotime('+4 hour', $now),  strtotime('+4 hour', $now)],
        ];
        $test1_consolidated = CRM_Resource_Timeframes::consolidate($test1);
        $this->assertEquals($test1, $test1_consolidated, 'this should have not been changed');

        // test 2: not nicely ordered and not overlapping
        $test2 = array_reverse($test1);
        $test2_consolidated = CRM_Resource_Timeframes::consolidate($test2);
        $this->assertEquals($test1, $test2_consolidated, 'this should have been equal to the test1 result (ordered)');

        // test 3: overlapping
        $test3 = [
            [strtotime('+0 hours', $now), strtotime('+2 hour', $now)],
            [strtotime('+1 hour', $now),  strtotime('+3 hour', $now)],
            [strtotime('+2 hour', $now),  strtotime('+4 hour', $now)],
        ];
        $test3_consolidated = CRM_Resource_Timeframes::consolidate($test3);
        $test3_expected = [[strtotime('+0 hours', $now), strtotime('+4 hour', $now)]];
        $this->assertEquals($test3_expected, $test3_consolidated, 'this should have been condensed into one frame');
    }

}
