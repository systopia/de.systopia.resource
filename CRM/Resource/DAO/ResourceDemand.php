<?php

/**
 * @package CRM
 * @copyright CiviCRM LLC https://civicrm.org/licensing
 *
 * Generated from de.systopia.resource/xml/schema/CRM/Resource/ResourceDemand.xml
 * DO NOT EDIT.  Generated by CRM_Core_CodeGen
 * (GenCodeChecksum:97cbd5e552a273c5362bc74cfd3c149b)
 */
use CRM_Resource_ExtensionUtil as E;

/**
 * Database access object for the ResourceDemand entity.
 */
class CRM_Resource_DAO_ResourceDemand extends CRM_Core_DAO {
  const EXT = E::LONG_NAME;
  const TABLE_ADDED = '';

  /**
   * Static instance to hold the table name.
   *
   * @var string
   */
  public static $_tableName = 'civicrm_resource_demand';

  /**
   * Field to show when displaying a record.
   *
   * @var string
   */
  public static $_labelField = 'label';

  /**
   * Should CiviCRM log any modifications to this table in the civicrm_log table.
   *
   * @var bool
   */
  public static $_log = TRUE;

  /**
   * Unique Resource Demand ID
   *
   * @var int|string|null
   *   (SQL type: int unsigned)
   *   Note that values will be retrieved from the database as a string.
   */
  public $id;

  /**
   * Resource Demand Label
   *
   * @var string|null
   *   (SQL type: varchar(255))
   *   Note that values will be retrieved from the database as a string.
   */
  public $label;

  /**
   * Resource Type ID
   *
   * @var int|string
   *   (SQL type: int unsigned)
   *   Note that values will be retrieved from the database as a string.
   */
  public $resource_type_id;

  /**
   * Number of resources required
   *
   * @var int|string|null
   *   (SQL type: int unsigned)
   *   Note that values will be retrieved from the database as a string.
   */
  public $count;

  /**
   * Resource linked entity ID
   *
   * @var int|string
   *   (SQL type: int unsigned)
   *   Note that values will be retrieved from the database as a string.
   */
  public $entity_id;

  /**
   * Resource linked entity table name
   *
   * @var string
   *   (SQL type: varchar(64))
   *   Note that values will be retrieved from the database as a string.
   */
  public $entity_table;

  /**
   * Marks demand templates
   *
   * @var bool|string|null
   *   (SQL type: tinyint)
   *   Note that values will be retrieved from the database as a string.
   */
  public $is_template;

  /**
   * Class constructor.
   */
  public function __construct() {
    $this->__table = 'civicrm_resource_demand';
    parent::__construct();
  }

  /**
   * Returns localized title of this entity.
   *
   * @param bool $plural
   *   Whether to return the plural version of the title.
   */
  public static function getEntityTitle($plural = FALSE) {
    return $plural ? E::ts('Resource Demands') : E::ts('Resource Demand');
  }

  /**
   * Returns foreign keys and entity references.
   *
   * @return array
   *   [CRM_Core_Reference_Interface]
   */
  public static function getReferenceColumns() {
    if (!isset(Civi::$statics[__CLASS__]['links'])) {
      Civi::$statics[__CLASS__]['links'] = static::createReferenceColumns(__CLASS__);
      Civi::$statics[__CLASS__]['links'][] = new CRM_Core_Reference_Dynamic(self::getTableName(), 'entity_id', NULL, 'id', 'entity_table');
      CRM_Core_DAO_AllCoreTables::invoke(__CLASS__, 'links_callback', Civi::$statics[__CLASS__]['links']);
    }
    return Civi::$statics[__CLASS__]['links'];
  }

  /**
   * Returns all the column names of this table
   *
   * @return array
   */
  public static function &fields() {
    if (!isset(Civi::$statics[__CLASS__]['fields'])) {
      Civi::$statics[__CLASS__]['fields'] = [
        'id' => [
          'name' => 'id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => E::ts('ID'),
          'description' => E::ts('Unique Resource Demand ID'),
          'required' => TRUE,
          'where' => 'civicrm_resource_demand.id',
          'table_name' => 'civicrm_resource_demand',
          'entity' => 'ResourceDemand',
          'bao' => 'CRM_Resource_DAO_ResourceDemand',
          'localizable' => 0,
          'html' => [
            'type' => 'Number',
          ],
          'readonly' => TRUE,
          'add' => NULL,
        ],
        'label' => [
          'name' => 'label',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => E::ts('Resource Demand Label'),
          'description' => E::ts('Resource Demand Label'),
          'maxlength' => 255,
          'size' => CRM_Utils_Type::HUGE,
          'where' => 'civicrm_resource_demand.label',
          'table_name' => 'civicrm_resource_demand',
          'entity' => 'ResourceDemand',
          'bao' => 'CRM_Resource_DAO_ResourceDemand',
          'localizable' => 1,
          'html' => [
            'type' => 'Text',
          ],
          'add' => NULL,
        ],
        'resource_type_id' => [
          'name' => 'resource_type_id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => E::ts('Resource Type'),
          'description' => E::ts('Resource Type ID'),
          'required' => TRUE,
          'where' => 'civicrm_resource_demand.resource_type_id',
          'table_name' => 'civicrm_resource_demand',
          'entity' => 'ResourceDemand',
          'bao' => 'CRM_Resource_DAO_ResourceDemand',
          'localizable' => 0,
          'html' => [
            'type' => 'Select',
          ],
          'pseudoconstant' => [
            'optionGroupName' => 'resource_types',
            'optionEditPath' => 'civicrm/admin/options/resource_types',
          ],
          'add' => NULL,
        ],
        'count' => [
          'name' => 'count',
          'type' => CRM_Utils_Type::T_INT,
          'title' => E::ts('Count'),
          'description' => E::ts('Number of resources required'),
          'where' => 'civicrm_resource_demand.count',
          'default' => '1',
          'table_name' => 'civicrm_resource_demand',
          'entity' => 'ResourceDemand',
          'bao' => 'CRM_Resource_DAO_ResourceDemand',
          'localizable' => 0,
          'html' => [
            'type' => 'Number',
          ],
          'add' => NULL,
        ],
        'entity_id' => [
          'name' => 'entity_id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => E::ts('Entity ID'),
          'description' => E::ts('Resource linked entity ID'),
          'required' => TRUE,
          'where' => 'civicrm_resource_demand.entity_id',
          'table_name' => 'civicrm_resource_demand',
          'entity' => 'ResourceDemand',
          'bao' => 'CRM_Resource_DAO_ResourceDemand',
          'localizable' => 0,
          'add' => NULL,
        ],
        'entity_table' => [
          'name' => 'entity_table',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => E::ts('Entity Table'),
          'description' => E::ts('Resource linked entity table name'),
          'required' => TRUE,
          'maxlength' => 64,
          'size' => CRM_Utils_Type::BIG,
          'where' => 'civicrm_resource_demand.entity_table',
          'table_name' => 'civicrm_resource_demand',
          'entity' => 'ResourceDemand',
          'bao' => 'CRM_Resource_DAO_ResourceDemand',
          'localizable' => 0,
          'pseudoconstant' => [
            'optionGroupName' => 'resource_demand_types',
            'optionEditPath' => 'civicrm/admin/options/resource_demand_types',
          ],
          'add' => NULL,
        ],
        'is_template' => [
          'name' => 'is_template',
          'type' => CRM_Utils_Type::T_BOOLEAN,
          'title' => E::ts('Is this a demand template?'),
          'description' => E::ts('Marks demand templates'),
          'import' => TRUE,
          'where' => 'civicrm_resource_demand.is_template',
          'export' => TRUE,
          'default' => '0',
          'table_name' => 'civicrm_resource_demand',
          'entity' => 'ResourceDemand',
          'bao' => 'CRM_Resource_DAO_ResourceDemand',
          'localizable' => 0,
          'html' => [
            'type' => 'CheckBox',
          ],
          'add' => NULL,
        ],
      ];
      CRM_Core_DAO_AllCoreTables::invoke(__CLASS__, 'fields_callback', Civi::$statics[__CLASS__]['fields']);
    }
    return Civi::$statics[__CLASS__]['fields'];
  }

  /**
   * Return a mapping from field-name to the corresponding key (as used in fields()).
   *
   * @return array
   *   Array(string $name => string $uniqueName).
   */
  public static function &fieldKeys() {
    if (!isset(Civi::$statics[__CLASS__]['fieldKeys'])) {
      Civi::$statics[__CLASS__]['fieldKeys'] = array_flip(CRM_Utils_Array::collect('name', self::fields()));
    }
    return Civi::$statics[__CLASS__]['fieldKeys'];
  }

  /**
   * Returns the names of this table
   *
   * @return string
   */
  public static function getTableName() {
    return CRM_Core_DAO::getLocaleTableName(self::$_tableName);
  }

  /**
   * Returns if this table needs to be logged
   *
   * @return bool
   */
  public function getLog() {
    return self::$_log;
  }

  /**
   * Returns the list of fields that can be imported
   *
   * @param bool $prefix
   *
   * @return array
   */
  public static function &import($prefix = FALSE) {
    $r = CRM_Core_DAO_AllCoreTables::getImports(__CLASS__, 'resource_demand', $prefix, []);
    return $r;
  }

  /**
   * Returns the list of fields that can be exported
   *
   * @param bool $prefix
   *
   * @return array
   */
  public static function &export($prefix = FALSE) {
    $r = CRM_Core_DAO_AllCoreTables::getExports(__CLASS__, 'resource_demand', $prefix, []);
    return $r;
  }

  /**
   * Returns the list of indices
   *
   * @param bool $localize
   *
   * @return array
   */
  public static function indices($localize = TRUE) {
    $indices = [];
    return ($localize && !empty($indices)) ? CRM_Core_DAO_AllCoreTables::multilingualize(__CLASS__, $indices) : $indices;
  }

}
