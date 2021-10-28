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

namespace Civi\Api4;

/**
 * ResourceDemand entity action
 *
 * Provided by the CiviCRM Resource Management extension.
 *
 * @package Civi\Api4
 */

namespace Civi\Api4\Action\Resource;

use Civi\Api4\Generic\AbstractAction;
use Civi\Api4\Generic\Result;
use CRM_Contact_BAO_Contact_Utils;
use CRM_Resource_ExtensionUtil as E;

/**
 * Generate a security checksum for anonymous access to CiviCRM.
 *
 * @method $this setId(int $cid) set resource ID (required)
 * @method int getId() get resource ID param
 * @method $this setDemand_id(int $cid) set demand ID (required)
 * @method int getDemand_id() get demand ID param
 */
class MeetsDemand extends AbstractAction
{

    /**
     * ID of the resource
     *
     * @var int
     * @required
     */
    protected $id;

    /**
     * ID of the resource demand
     *
     * @var int
     * @required
     */
    protected $resource_demand_id;

    /**
     * Can this result be cached
     *
     * @var bool
     */
    protected $cached = true;

    /**
     * Run this action
     *
     * @param \Civi\Api4\Generic\Result $result
     */
    public function _run(Result $result)
    {
        // get resource
        $resource = new \CRM_Resource_BAO_Resource();
        $resource->id = $this->id;
        if (!$resource->find(true)) {
            throw new \API_Exception("A resource with ID [{$this->id}] doesn't exist (any more).");
        }

        // get demand
        $resource_demand = new \CRM_Resource_BAO_ResourceDemand();
        $resource_demand->id = $this->resource_demand_id;
        if (!$resource_demand->find(true)) {
            throw new \API_Exception("A resource demand with ID [{$this->resource_demand_id}] doesn't exist (any more).");
        }

        // check issues
        $violations = [];
        $fulfilled = $resource_demand->isFulfilledWithResource($resource, $this->cached, $violations);
        if (!$fulfilled && empty($violations)) {
            // add a general violation
            $violations[] = E::ts("At least one criteria was not met.");
        }

        $result[] = [
            'meets_demand' => $fulfilled,
            'violations' => $violations,
            'id' => $this->id,
            'resource_demand_id' => $this->resource_demand_id,
        ];
    }

}
