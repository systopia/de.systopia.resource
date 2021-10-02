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

include 'ResourceTestBase.php';

/**
 * Resource API Test Case
 *
 * @group headless
 */
class BasicResourceTest extends ResourceTestBase implements HeadlessInterface, HookInterface,
                                                                      TransactionalInterface
{
    public function testCreateContactResource()
    {
        $contact = $this->createContact();
        $resource = $this->traitCallAPISuccess('Resource', 'create', [
            'entity_id' => $contact['id'],
            'entity_table' => 'civicrm_contact',
            'label' => $this->randomString()
        ]);
    }
}
