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

# noinspection SqlNoDataSourceInspectionForFile

CREATE TABLE IF NOT EXISTS `civicrm_resource` (
    `id`           int unsigned     NOT NULL AUTO_INCREMENT  COMMENT 'Unique Resource ID',
    `label`        varchar(255)                              COMMENT 'Resource Label',
    `resource_type_id` int unsigned NOT NULL                 COMMENT 'Resource Type ID',
    `entity_id`    int unsigned     NOT NULL                 COMMENT 'Resource - linked entity ID',
    `entity_table` varchar(64)      NOT NULL                 COMMENT 'Resource - linked entity table name' ,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS  `civicrm_resource_demand` (
    `id`               int unsigned   NOT NULL AUTO_INCREMENT  COMMENT 'Unique Resource Demand ID',
    `label`            varchar(255)                            COMMENT 'Resource Label',
    `resource_type_id` int unsigned   NOT NULL                 COMMENT 'Resource Type ID',
    `count`            int unsigned   DEFAULT 1                COMMENT 'Resource Demand Count',
    `entity_id`        int unsigned   NOT NULL                 COMMENT 'Resource linked entity ID',
    `entity_table`     varchar(64)    NOT NULL                 COMMENT 'Resource linked entity table name',
    `is_template`      tinyint        DEFAULT 0                COMMENT 'Marks demand templates',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `civicrm_resource_assignment` (
  `id`                   int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique Resource Assignment ID',
  `resource_id`          int unsigned NOT NULL                 COMMENT 'Resource ID',
  `resource_demand_id`   int unsigned NOT NULL                 COMMENT 'Resource Demand ID',
  `status`               tinyint      NOT NULL                 COMMENT 'Resource Demand Status: 1=proposed, 2=denied, 3=confirmed',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS  `civicrm_resource_demand_condition` (
    `id`                  int unsigned   NOT NULL AUTO_INCREMENT  COMMENT 'Unique Resource Demand Condition ID',
    `resource_demand_id`  int unsigned   NOT NULL                 COMMENT 'Resource Demand ID',
    `class_name`          varchar(127)                            COMMENT 'Class name of the implementation, a subclass of CRM_Resource_BAO_Resource_Unavailability',
    `parameters`          varchar(255)                            COMMENT 'A json encoded data blob to store the parameters of this specific unavailability',
    PRIMARY KEY (`id`),
    CONSTRAINT FK_civicrm_resource_demand_condition_resource_demand_id FOREIGN KEY (`resource_demand_id`) REFERENCES `civicrm_resource_demand`(`id`) ON DELETE CASCADE
)  ENGINE=InnoDB;


CREATE TABLE IF NOT EXISTS  `civicrm_resource_unavailability` (
   `id`                int unsigned   NOT NULL AUTO_INCREMENT  COMMENT 'Unique Resource Unavailability ID',
   `reason`            varchar(255)                            COMMENT 'Reason for the unavailability',
   `resource_id`       int unsigned   NOT NULL                 COMMENT 'Resource Demand ID',
   `class_name`        varchar(127)                            COMMENT 'Class name of the implementation, a subclass of CRM_Resource_BAO_Resource_Unavailability',
   `parameters`        varchar(255)                            COMMENT 'A json encoded data blob to store the parameters of this specific unavailability',
   PRIMARY KEY (`id`),
   CONSTRAINT FK_civicrm_resource_unavailability_resource_id FOREIGN KEY (`resource_id`) REFERENCES `civicrm_resource`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;
