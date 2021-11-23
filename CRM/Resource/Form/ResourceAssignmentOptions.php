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
class CRM_Resource_Form_ResourceAssignmentOptions extends CRM_Core_Form
{
    /** @var integer resource */
    protected $resource_id = null;

    /** @var CRM_Resource_BAO_Resource resource */
    protected $resource = null;

    /** @var string cache key */
    protected $search_result = null;

    public function buildQuickForm()
    {
        // get parameters
        $this->resource_id = CRM_Utils_Request::retrieve('resource_id', 'Integer', $this);

        // load the resource demand
        $this->resource = new CRM_Resource_BAO_Resource();
        $this->resource->id = $this->resource_id;
        $this->resource->find();
        $this->resource->fetch(true);

        // set title
        $this->setTitle(E::ts("Assign \"%1\"", [1 => $this->resource->label]));

        // find demand candidates
        // remark: this is unrestricted b/c Fabian said that's ok for the moment.
        $assignments = $this->resource->getAssignedDemands();
        $assigned_demands = [];
        foreach ($assignments as $assignment) {
            $assigned_demands[$assignment->id] = true;
        }

        $candidates = $this->resource->getDemandCandidates();

        // prep for display
        $display_candidates = [];
        foreach ($candidates as $candidate) {
            /** @var CRM_Resource_BAO_ResourceDemand $candidate */
            if (isset($assigned_demands[$candidate->id])) {
                continue; // this one's already assigned
            }
            $display_candidate = $candidate->toArray();
            $display_candidate['id'] = $candidate->id;
            $display_candidate['field_name'] = "assign_{$candidate->id}";
            $display_candidate['demand_label'] = $candidate->getEntityLabel();
            $display_candidates[] = $display_candidate;
        }
        $this->assign('candidates', $display_candidates);
        $this->assign('resource_id',  $this->resource_id);

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

        Civi::resources()->addStyleUrl(E::url('css/resource_assign.css'), 10, 'page-header');
        Civi::resources()->addScriptUrl(E::url('js/resource_assign.js'), 10, 'page-header');

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
                    $resource_demand_id = substr($key, 7);
                    $total_count++;
                    try {
                        civicrm_api3('ResourceAssignment', 'create', [
                            'resource_id' => $this->resource_id,
                            'resource_demand_id' => $resource_demand_id,
                            'status' => CRM_Resource_BAO_ResourceAssignment::STATUS_CONFIRMED,
                        ]);
                        $success_count++;
                    } catch (CiviCRM_API3_Exception $ex) {
                        $error_messages[$resource_demand_id] = $ex->getMessage();
                    }
                }
            }
        }

        if ($total_count) {
            CRM_Core_Session::setStatus(
                E::ts("Assigned %1 demands to this resource.", [1 => $success_count]),
                E::ts("Success"),
                'info'
            );
            if ($success_count < $total_count) {
                CRM_Core_Session::setStatus(
                    E::ts("%1 demands could not be assigned this resource. Errors were: <br/><ul><li>%2</li></ul>", [
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
