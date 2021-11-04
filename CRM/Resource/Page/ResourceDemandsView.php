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

class CRM_Resource_Page_ResourceDemandsView extends CRM_Core_Page
{
    /** @var integer entity ID */
    public $entity_id = null;

    /** @var integer entity table
    protected $entity_table = null;

    /** @var array of CRM_Resource_BAO_ResourceDemand  */
    protected $resource_demands = null;

    /** @var array assignment data */
    protected $assignments = null;

    /** @var object instance to assign variables to, by default this page */
    protected $assign_sink = null;

    /**
     * Set the referred entity, overrides anything retrieved from the url/post
     * @param string $entity_table
     * @param integer $entity_id
     */
    public function setEntity($entity_table, $entity_id)
    {
        $this->entity_id = $entity_id;
        $this->entity_table = $entity_table;
    }

    public function run()
    {
        $this->assignData();
        parent::run();
    }

    public function assignData()
    {
        // init basics
        if (!isset($this->data_sink))
            $this->data_sink = $this;
        if (!isset($this->entity_id))
            $this->entity_id = CRM_Utils_Request::retrieve('entity_id', 'Integer', $this);
        if (!isset($this->entity_table))
            $this->entity_table = CRM_Utils_Request::retrieve('entity_id', 'String', $this);

        // fetch all resource demands
        $demand_query = new CRM_Resource_BAO_ResourceDemand();
        $demand_query->entity_table = $this->entity_table;
        $demand_query->entity_id = $this->entity_id;
        $this->resource_demands = $demand_query->fetchAll();

        Civi::resources()->addStyleUrl(E::url('css/resource_demand_view.css'));
        Civi::resources()->addScriptUrl(E::url('js/resource_demand_view.js'));
    }

    /**
     * Set a custom assign() sink
     */
    public function setAssignSink($assign_sink) {
        $this->assign_sink = $assign_sink;
    }

}
