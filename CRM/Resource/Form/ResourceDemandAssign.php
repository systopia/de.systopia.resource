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

        // set title
        $this->setTitle(E::ts("Assign More \"%1\" Resources", [1 => $this->resource_demand->label]));

        // gather general information
        $currently_assigned = $this->resource_demand->getAssignmentCount();
        $this->assign('assigned_now', $currently_assigned);
        $this->assign('assigned_missing', max($this->resource_demand->count - $currently_assigned, 0));
        $this->assign('assigned_requested', $this->resource_demand->count);
        $this->assign('demand_label', $this->resource_demand->label);

        // find candidates, but only once for this form to avoid differing results during postprocessing.
        $candidates = $this->get('candidates');
        if (!isset($candidates)) {
            $count = max($this->resource_count, $this->resource_demand->count);
            $candidates = $this->resource_demand->getResourceCandidates($count);
            $this->set('candidates', $candidates);
        }

        // prep for display
        $display_candidates = [];
        foreach ($candidates as $candidate) {
            /** @var CRM_Resource_BAO_Resource $candidate */
            $display_candidate = $candidate->toArray();
            $display_candidate['id'] = $candidate->id;
            $display_candidate['field_name'] = "assign_{$candidate->id}";
            $display_candidate['paths']['view'] = $candidate->getEntityUrl('view');
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

        Civi::resources()->addVars('resource_demand_assign', [
            'assigned_missing' => max($this->resource_demand->count - $currently_assigned, 0)
        ]);
        Civi::resources()->addStyleFile(E::LONG_NAME, 'css/resource_demand_assign.css', 10, 'page-header');
        Civi::resources()->addScriptFile(E::LONG_NAME, 'js/resource_demand_assign.js', 10, 'page-header');

        parent::buildQuickForm();
    }

    public function postProcess()
    {
        $values = $this->exportValues();

        $total_count = 0;
        $success_count = 0;
        $error_messages = [];
        foreach ($values as $key => $value) {
            if (!empty($value)) {
                if (substr($key, 0, 7) == 'assign_') {
                    $resource_id = substr($key, 7);
                    $total_count++;
                    try {
                        civicrm_api3('ResourceAssignment', 'create', [
                            'resource_id' => $resource_id,
                            'resource_demand_id' => $this->resource_demand_id,
                            'status' => CRM_Resource_BAO_ResourceAssignment::STATUS_CONFIRMED,
                        ]);
                        $success_count++;
                    } catch (CiviCRM_API3_Exception $ex) {
                        $error_messages[$resource_id] = $ex->getMessage();
                    }
                }
            }
        }

        if ($total_count) {
            CRM_Core_Session::setStatus(
                E::ts("Assigned %1 resources to this demand.", [1 => $success_count]),
                E::ts("Success"),
                'info'
            );
            if ($success_count < $total_count) {
                CRM_Core_Session::setStatus(
                    E::ts("%1 resources could not be assigned this demand. Errors were: <br/><ul><li>%2</li></ul>", [
                        1 => $total_count - $success_count,
                        2 => implode("</li><li>", $error_messages)
                    ]),
                    E::ts("Failure"),
                    'warn'
                );
            }
        }

        parent::postProcess();
    }

}
