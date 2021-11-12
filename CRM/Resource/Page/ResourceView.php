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

    /** @var \CRM_Resource_BAO_Resource resource */
    protected $resource = null;

    /** @var array unavailability data */
    protected $unavailabilities = null;

    /** @var array assignment data */
    protected $assignments = null;

    public function run()
    {
        $this->id = CRM_Utils_Request::retrieve('id', 'Integer', $this);
        // load resource
        $this->resource = CRM_Resource_BAO_Resource::getInstance($this->id);
        $now = date('YmdHis');

        // load and prep unavailabilities
        $this->unavailabilities = $this->resource->getUnavailabilities();
        $unavailability_list = [];
        foreach ($this->unavailabilities as $unavailability) {
            $unavailability_data = $unavailability->toArray();
            $unavailability_data['display_name'] = $unavailability->getLabel();
            $unavailability_data['active_now'] = $unavailability->isActive($now, $now);
            $unavailability_data['edit_link'] = CRM_Utils_System::url('civicrm/resource/unavailability/edit', "id={$unavailability_data['id']}");
            $unavailability_list[] = $unavailability_data;
        }

        // load assignments
        $this->assignments = [];
        $assigned_demands = $this->resource->getAssignedDemands();
        foreach ($assigned_demands as $assignment_id => $demand) {
            /** @var  CRM_Resource_BAO_ResourceDemand $demand */
            $this->assignments[] = [
                'assignment_id' => $assignment_id,
                'id' => $demand->id,
                'name' => $demand->label,
                'entity_label' => $demand->getEntityLabel(),
                'time' => $demand->getRenderedTimeframe(),
                'status' => E::ts("assigned"), // todo
            ];
        }

        // enrich data
        $this->assign('resource_type_label', CRM_Resource_Types::getType($this->resource->resource_type_id)['label']);
        $this->assign('resource_label', $this->resource->label);

        // pass data to smarty
        $this->assign('resource', $this->resource);
        $this->assign('unavailabilities', $unavailability_list);
        $this->assign('assignments', $this->assignments);
        $this->assign('is_available', $this->resource->isAvailable(date('YmdHis'), date('YmdHis')));


        // generate links
        $this->assign('unavailability_create_link',
                      CRM_Utils_System::url('civicrm/resource/unavailability/create', "resource_id={$this->id}"));

        Civi::resources()->addStyleUrl(E::url('css/resource_view.css'), 10, 'page-header');
        Civi::resources()->addScriptUrl(E::url('js/resource_view.js'), 10, 'page-header');

        parent::run();
    }

}
