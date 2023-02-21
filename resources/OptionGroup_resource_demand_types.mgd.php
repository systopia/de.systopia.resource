<?php

return [
  [
    'name' => 'OptionGroup_resource_demand_types',
    'entity' => 'OptionGroup',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'resource_demand_types',
        'title' => 'Resource Demand Types',
        'description' => NULL,
        'data_type' => NULL,
        'is_reserved' => TRUE,
        'is_active' => TRUE,
        'is_locked' => FALSE,
        'option_value_fields' => [
          'name',
          'label',
          'description',
        ],
      ],
      'match' => ['name'],
    ],
  ],
  [
    'name' => 'OptionGroup_resource_demand_types_OptionValue_Event_Demand',
    'entity' => 'OptionValue',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'option_group_id.name' => 'resource_demand_types',
        'label' => 'Event Demand',
        'value' => 'civicrm_event',
        'name' => 'Event_Demand',
        'grouping' => NULL,
        'filter' => 0,
        'is_default' => FALSE,
        'description' => 'A resource demand based on a event',
        'is_optgroup' => FALSE,
        'is_reserved' => TRUE,
        'is_active' => TRUE,
        'component_id' => NULL,
        'domain_id' => NULL,
        'visibility_id' => NULL,
        'icon' => NULL,
        'color' => NULL,
      ],
      'match' => ['option_group_id', 'name'],
    ],
  ],
];
