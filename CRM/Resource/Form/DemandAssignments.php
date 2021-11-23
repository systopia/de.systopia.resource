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
class CRM_Resource_Form_DemandAssignments extends CRM_Core_Form
{
    /** @var integer resource demand */
    protected $resource_demand_id = null;

    /** @var CRM_Resource_BAO_ResourceDemand resource demand */
    protected $resource_demand = null;

    /** @var  */
    protected $assigned_resources = null;

    public function buildQuickForm()
    {
        // get parameters
        $this->resource_demand_id = CRM_Utils_Request::retrieve('resource_demand_id', 'Integer', $this);

        // load the resource demand
        $this->resource_demand = new CRM_Resource_BAO_ResourceDemand();
        $this->resource_demand->id = $this->resource_demand_id;
        $this->resource_demand->find();
        $this->resource_demand->fetch(true);

        // set title
        $this->setTitle(E::ts("Resources currently assigned to '%1'", [1 => $this->resource_demand->label]));

        // load current assignments
        $this->assigned_resources = $this->resource_demand->getAssignedResources();

        // prep for display
        $display_resources = [];
        foreach ($this->assigned_resources as $resource) {
            /** @var CRM_Resource_BAO_Resource $resource */
            $display_candidate = $resource->toArray();
            $display_candidate['id'] = $resource->id;
            $display_candidate['field_name'] = "unassign_{$resource->id}";
            $display_candidate['meets_demand'] = $this->resource_demand->isFulfilledWithResource($resource);
            $display_resources[] = $display_candidate;
        }

        // assign some stuff
        $this->assign('resources', $display_resources);
        $this->assign('required_count', $this->resource_demand->count);
        $this->assign('assigned_count', count($this->assigned_resources));

        // add checkboxes
        foreach ($display_resources as $assigned_resource) {
            $this->add(
                'checkbox',
                $assigned_resource['field_name'],
                ''
            );
        }

        $this->addButtons([
              [
                  'type' => 'submit',
                  'name' => E::ts('Unassign Selected'),
                  'isDefault' => true,
              ],
          ]);

        Civi::resources()->addVars('resource_demand_assign', [
            'assigned_missing' => max($this->resource_demand->count - $currently_assigned, 0),
        ]);
        Civi::resources()->addStyleUrl(E::url('css/demand_unassign.css'), 10, 'page-header');
        Civi::resources()->addScriptUrl(E::url('js/demand_unassign.js'), 10, 'page-header');

        parent::buildQuickForm();
    }

    /**
     * Postprocess, i.e. delete the assignments identified by the resource IDs
     *
     * @todo this could probably do with some performance improvements...
     */
    public function postProcess()
    {
        $values = $this->exportValues();

        $success_count = 0;
        $error_messages = [];
        $resource_ids_to_be_removed = [];
        foreach ($values as $key => $value) {
            if (!empty($value)) {
                if (substr($key, 0, 9) == 'unassign_') {
                    $resource_id = (int) substr($key, 9);
                    $resource_ids_to_be_removed[] = $resource_id;
                }
            }
        }
        $total_count = count($resource_ids_to_be_removed);

        // delete them
        foreach ($this->assigned_resources as $resource) {
            if (in_array($resource->id, $resource_ids_to_be_removed)) {
                // find and delete the assignment
                try {
                    $assignment = new CRM_Resource_BAO_ResourceAssignment();
                    $assignment->resource_id = $resource->id;
                    $assignment->resource_demand_id = $this->resource_demand_id;
                    $assignment->status = CRM_Resource_BAO_ResourceAssignment::STATUS_CONFIRMED;
                    $assignment->find(true);
                    $assignment->delete();
                    $success_count++;
                } catch (Exception $ex) {
                    $error_messages[] = $ex->getMessage();
                }
            }
        }

        if ($total_count) {
            CRM_Core_Session::setStatus(
                E::ts("Removed (unassigned) %1 resources from this demand.", [1 => $success_count]),
                E::ts("Success"),
                'info'
            );
            if ($success_count < $total_count) {
                CRM_Core_Session::setStatus(
                    E::ts("%1 resources could not be unassigned from this demand. Errors were: <br/><ul><li>%2</li></ul>", [
                        1 => $total_count - $success_count,
                        2 => implode("</li><li>", $error_messages),
                    ]),
                    E::ts("Failure"),
                    'warn'
                );
            }
        }

        parent::postProcess();
    }

}
