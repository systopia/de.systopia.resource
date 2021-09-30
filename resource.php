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

require_once 'resource.civix.php';

// phpcs:disable
use CRM_Resource_ExtensionUtil as E;

// phpcs:enable

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function resource_civicrm_config(&$config)
{
    _resource_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_xmlMenu
 */
function resource_civicrm_xmlMenu(&$files)
{
    _resource_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function resource_civicrm_install()
{
    _resource_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postInstall
 */
function resource_civicrm_postInstall()
{
    _resource_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_uninstall
 */
function resource_civicrm_uninstall()
{
    _resource_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function resource_civicrm_enable()
{
    _resource_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_disable
 */
function resource_civicrm_disable()
{
    _resource_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_upgrade
 */
function resource_civicrm_upgrade($op, CRM_Queue_Queue $queue = null)
{
    return _resource_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_managed
 */
function resource_civicrm_managed(&$entities)
{
    _resource_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Add CiviCase types provided by this extension.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_caseTypes
 */
function resource_civicrm_caseTypes(&$caseTypes)
{
    _resource_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Add Angular modules provided by this extension.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_angularModules
 */
function resource_civicrm_angularModules(&$angularModules)
{
    // Auto-add module files from ./ang/*.ang.php
    _resource_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_alterSettingsFolders
 */
function resource_civicrm_alterSettingsFolders(&$metaDataFolders = null)
{
    _resource_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_entityTypes
 */
function resource_civicrm_entityTypes(&$entityTypes)
{
    _resource_civix_civicrm_entityTypes($entityTypes);
    $entityTypes[] = [
        'name'  => 'Resource',
        'class' => 'CRM_Resource_DAO_Resource',
        'table' => 'civicrm_resource',
    ];
    $entityTypes[] = [
        'name'  => 'ResourceAssignment',
        'class' => 'CRM_Resource_DAO_ResourceAssignment',
        'table' => 'civicrm_resource_assignment',
    ];
    $entityTypes[] = [
        'name'  => 'ResourceDemand',
        'class' => 'CRM_Resource_DAO_ResourceDemand',
        'table' => 'civicrm_resource_demand',
    ];
    $entityTypes[] = [
        'name'  => 'ResourceDemandCondition',
        'class' => 'CRM_Resource_DAO_ResourceDemandCondition',
        'table' => 'civicrm_resource_demand_condition',
    ];
    $entityTypes[] = [
        'name'  => 'ResourceUnavailability',
        'class' => 'CRM_Resource_DAO_ResourceUnavailability',
        'table' => 'civicrm_resource_unavailability',
    ];
}

/**
 * Implements hook_civicrm_themes().
 */
function resource_civicrm_themes(&$themes)
{
    _resource_civix_civicrm_themes($themes);
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_preProcess
 */
//function resource_civicrm_preProcess($formName, &$form) {
//
//}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_navigationMenu
 */
//function resource_civicrm_navigationMenu(&$menu) {
//  _resource_civix_insert_navigation_menu($menu, 'Mailings', [
//    'label' => E::ts('New subliminal message'),
//    'name' => 'mailing_subliminal_message',
//    'url' => 'civicrm/mailing/subliminal',
//    'permission' => 'access CiviMail',
//    'operator' => 'OR',
//    'separator' => 0,
//  ]);
//  _resource_civix_navigationMenu($menu);
//}
