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
     `id`           int unsigned  NOT NULL AUTO_INCREMENT  COMMENT 'Unique Resource ID',
     `label`        varchar(255)                           COMMENT 'Resource Label',
     `entity_id`    int unsigned  NOT NULL                 COMMENT 'Resource - linked entity ID',
     `entity_table` varchar(64)   NOT NULL                 COMMENT 'Resource - linked entity table name' ,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB;

 