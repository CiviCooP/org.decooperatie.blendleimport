<?php

/**
 * Class CRM_BlendleImport_DAO_ImportJob.
 * DAO for table civicrm_blendleimport_job.
 *
 * @author Kevin Levie <kevin.levie@civicoop.org>
 * @package org.decooperatie.blendleimport
 * @license AGPL-3.0
 *
 * @property int $id
 * @property string $name
 * @property string $publication
 * @property string $import_date
 * @property int $add_tag
 * @property int $add_membership_type
 * @property string $status
 * @property string $created_date
 * @property int $created_user_id
 */
class CRM_BlendleImport_DAO_ImportJob extends CRM_Core_DAO {

  /**
   * Status IDs used in $this->status.
   * Contacts/activities/contributions means: processing X is the next step.
   */
  const STATUS_NEW = 'new';
  const STATUS_CONTACTS = 'contacts';
  const STATUS_ACTIVITIES = 'activities';
  const STATUS_TAGSMEMB = 'tagsmemb';
  const STATUS_PAYMENTS = 'payments';
  const STATUS_COMPLETE = 'complete';

  /**
   * Cached fields data, see functions below.
   */
  protected static $_fields = NULL;
  protected static $_fieldKeys = NULL;
  protected static $_export = NULL;

  /**
   * Get table name.
   * @return string
   */
  public static function getTableName() {
    return 'civicrm_blendleimport_job';
  }

  /**
   * Return all columns for this table.
   * @return array
   */
  public static function &fields() {
    if (!(self::$_fields)) {
      self::$_fields = [
        'id'                  => [
          'name'     => 'id',
          'type'     => CRM_Utils_Type::T_INT,
          'title'    => ts('Import Job ID'),
          'required' => TRUE,
        ],
        'name'                => [
          'name'      => 'name',
          'type'      => CRM_Utils_Type::T_STRING,
          'maxlength' => 255,
          'title'     => ts('Import Job Name'),
          'required'  => TRUE,
        ],
        'publication'         => [
          'name'      => 'publication',
          'type'      => CRM_Utils_Type::T_STRING,
          'maxlength' => 100,
          'title'     => ts('Publication Name'),
          'default'   => 'NULL',
        ],
        'import_date'         => [
          'name'        => 'import_date',
          'type'        => CRM_Utils_Type::T_DATE + CRM_Utils_Type::T_TIME,
          'title'       => ts('Import Date'),
          'dataPattern' => '/^\d{4}-?\d{2}-?\d{2} ?(\d{2}:?\d{2}:?(\d{2})?)?$/',
          'default'     => 'NULL',
        ],
        'add_tag'             => [
          'name'        => 'add_tag',
          'type'        => CRM_Utils_Type::T_INT,
          'title'       => ts('Add Tag (ID)'),
          'FKClassName' => 'CRM_Core_DAO_Tag',
          'default'     => 'NULL',
        ],
        'add_membership_type' => [
          'name'        => 'add_membership_type',
          'type'        => CRM_Utils_Type::T_INT,
          'title'       => ts('Create Membership (Type ID)'),
          'FKClassName' => 'CRM_Member_DAO_MembershipType',
          'default'     => 'NULL',
        ],
        'status'              => [
          'name'    => 'status',
          'type'    => CRM_Utils_Type::T_STRING,
          'title'   => ts('Job Status'),
          'default' => 'NULL',
        ],
        'created_date'        => [
          'name'    => 'created_date',
          'type'    => CRM_Utils_Type::T_DATE + CRM_Utils_Type::T_TIME,
          'title'   => ts('Created Date'),
          'default' => 'NULL',
        ],
        'created_id'          => [
          'name'        => 'created_id',
          'type'        => CRM_Utils_Type::T_INT,
          'title'       => ts('Created By'),
          'FKClassName' => 'CRM_Contact_DAO_Contact',
          'default'     => 'NULL',
        ],
      ];
    }
    return self::$_fields;
  }

  /**
   * Returns an array containing, for each field, the array key used for that.
   * field in self::$_fields.
   * @return array
   */
  public static function &fieldKeys() {
    if (!(self::$_fieldKeys)) {
      $fields = self::fields();
      self::$_fieldKeys = [];
      foreach ($fields as $name => $field) {
        self::$_fieldKeys[$name] = $field['name'];
      }
    }
    return self::$_fieldKeys;
  }

  /**
   * Returns the list of fields that can be exported.
   * @param mixed $prefix
   * @return array
   */
  public static function &export($prefix = FALSE) {
    if (!(self::$_export)) {
      self::$_export = [];
      $fields = self::fields();
      foreach ($fields as $name => $field) {
        if (CRM_Utils_Array::value('export', $field)) {
          if ($prefix) {
            self::$_export['activity'] = &$fields[$name];
          } else {
            self::$_export[$name] = &$fields[$name];
          }
        }
      }
    }
    return self::$_export;
  }
}