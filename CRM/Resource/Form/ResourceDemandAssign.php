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
 * Form controller class
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/quickform/
 */
class CRM_Resource_Form_ResourceDemandAssign extends CRM_Core_Form
{
    /** @var integer resource demand */
    protected $resource_demand_id = null;

    /** @var CRM_Resource_BAO_ResourceDemand resource demand */
    protected $resource_demand = null;

    /** @var integer resource count */
    protected $resource_count = 20;

    /** @var string cache key */
    protected $search_result = null;


    public function buildQuickForm()
    {
        // get parameters
        $this->resource_demand_id = CRM_Utils_Request::retrieve('resource_demand_id', 'Integer', $this);
        //$this->resource_count = CRM_Utils_Request::retrieve('resource_count', 'Integer', $this);

        // load the resource demand
        $this->resource_demand = new CRM_Resource_BAO_ResourceDemand();
        $this->resource_demand->id = $this->resource_demand_id;
        $this->resource_demand->find();
        $this->resource_demand->fetch(true);

        // find candidates
        $count = max($this->resource_count, $this->resource_demand->count);
        $candidates = $this->resource_demand->getResourceCandidates($count);

        // prep for display
        $display_candidates = [];
        foreach ($candidates as $candidate) {
            /** @var CRM_Resource_BAO_Resource $candidate */
            $display_candidate = $candidate->toArray();
            $display_candidate['id'] = $candidate->id;
            $display_candidate['field_name'] = "assign_{$candidate->id}";
            $display_candidates[] = $display_candidate;
        }
        $this->assign('candidates', $display_candidates);

        // add checkboxes
        foreach ($display_candidates as $display_candidate) {
            $this->add(
                'checkbox',
                $display_candidate['field_name'],
                ''
            );
        }

        $this->addButtons([
              [
                  'type' => 'submit',
                  'name' => E::ts('Assign Selected'),
                  'isDefault' => true,
              ],
          ]);

        parent::buildQuickForm();
    }

    public function postProcess()
    {
        $values = $this->exportValues();
        foreach ($values as $key => $value) {
            if (!empty($value)) {
                if (substr($key, 0, 7) == 'assign_') {
                    $resource_id = substr($key, 7);
                    civicrm_api3('ResourceAssignment', 'create', [
                        'resource_id' => $resource_id,
                        'resource_demand_id' => $this->resource_demand_id,
                        'status' => CRM_Resource_BAO_ResourceAssignment::STATUS_CONFIRMED,
                    ]);
                }
            }
        }
        parent::postProcess();
    }

}
