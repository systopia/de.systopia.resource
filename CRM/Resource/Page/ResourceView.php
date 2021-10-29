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

class CRM_Resource_Page_ResourceView extends CRM_Core_Page
{
    /** @var integer resource ID */
    protected $id = null;

    /** @var array resource data */
    protected $resource = null;

    /** @var array unavailability data */
    protected $unavailabilities = null;

    /** @var array assignment data */
    protected $assignments = null;

    public function run()
    {
        $this->id = CRM_Utils_Request::retrieve('id', 'Integer', $this);
        // load resource
        $this->resource = \Civi\Api4\Resource::get()
            ->addWhere('id', '=', $this->id)
            ->setLimit(1)->execute()->first();

        // load unavailabilities
        $this->unavailabilities = \Civi\Api4\ResourceUnavailability::get()
            ->addWhere('resource_id', '=', $this->id)
            ->execute()
            ->getArrayCopy();

        // load assignments
        $this->assignments = \Civi\Api4\ResourceAssignment::get()
            ->addWhere('resource_id', '=', $this->id)
            ->execute()
            ->getArrayCopy();

        $this->assign('resource', $this->resource);
        $this->assign('unavailabilities', $this->unavailabilities);
        $this->assign('assignments', $this->assignments);

        parent::run();
    }

}
