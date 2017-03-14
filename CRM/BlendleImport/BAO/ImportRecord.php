<?php

/**
 * Class CRM_BlendleImport_BAO_ImportRecord.
 * BAO for table civicrm_blendleimport_records.
 *
 * @author Kevin Levie <kevin.levie@civicoop.org>
 * @package org.decooperatie.blendleimport
 * @license AGPL-3.0
 */
class CRM_BlendleImport_BAO_ImportRecord extends CRM_BlendleImport_DAO_ImportRecord {

  /**
   * Fetch an array of Import Record rows.
   * @param array $params Input parameters to find object(s).
   * @param bool $asArray Whether to return an array of arrays instead of objects.
   * @return static[]|null The found object(s) or null
   */
  public static function getRecords($params = [], $asArray = FALSE) {
    $result = [];
    $instance = new static;

    // Parent defaults to null (meaning each unique author will only be returned once)
    if(!isset($params['parent'])) {
      $params['parent'] = ['IS NULL' => TRUE];
    }

    // Quick hack: not sure how to best support IS NULL, added manually this way
    foreach($params as $paramName => $param) {
      if(is_array($param) && isset($param['IS NULL'])) {
        $instance->whereAdd(CRM_Utils_Type::escape($paramName, 'String') . ' IS NULL');
        unset($params[$paramName]);
      }
    }

    // Find and fetch items
    if (!empty($params)) {
      $instance->copyValues($params);
    }
    $instance->find();

    while ($instance->fetch()) {
      if ($asArray) {
        $result[$instance->id] = static::recordToArray($instance);
      } else {
        $result[$instance->id] = clone $instance;
      }
    }

    return $result;
  }

  /**
   * Count import records.
   * @param array $params Input parameters to find object(s).
   * @return int Record Count
   */
  public static function getRecordCount($params = []) {
    $instance = new static;
    if (!empty($params)) {
      $instance->copyValues($params);
    }
    return $instance->count();
  }

  /**
   * Clear import records for a certain import job.
   * @param int $job_id Job ID
   * @return bool Success
   */
  public static function clearRecordsForJob($job_id) {
    $record = new static;
    $record->whereAdd('job_id = ' . (int)$job_id);
    return $record->delete(true);
  }

  /**
   * Create / update an import record based on array-data.
   * @param array $params key-value pairs
   * @param bool $returnObject Return full created object?
   * @return static|mixed Create result
   * @throws CRM_BlendleImport_Exception If parameters are obviously invalid
   */
  public static function create($params, $returnObject = FALSE) {
    $instance = new static;

    $hook = empty($params['id']) ? 'create' : 'edit';
    CRM_Utils_Hook::pre($hook, get_class($instance), CRM_Utils_Array::value('id', $params), $params);

    // Check and fix parameters
    if (isset($params['id']) && $params['id'] < 1) {
      throw new CRM_BlendleImport_Exception('Invalid value for id: ' . $params['id'] . '.');
    }
    if (isset($params['state']) && !in_array($params['state'], [ 'chosen', 'multiple', 'impossible' ])) {
      throw new CRM_BlendleImport_Exception('Invalid value for state: ' . $params['state'] . '.');
    }
    if($params['contact_id'] == 0) {
      $params['contact_id'] = NULL;
    }

    // Handle server side state change if updating, similar to CsvImportHelper?
    // Not currently implemented since it sort of works as-is... TODO refactor this function?

    // Update this row
    if(!empty($params['resolution']) && is_array($params['resolution'])) {
      $params['resolution'] = serialize($params['resolution']);
    }
    $instance->copyValues($params);
    $instance->save();

    // What is the best way to reload + return object? The best I could think of:
    $instance->find(TRUE);

    // Update all records with the same author name
    $instance->updateChildren();

    // That's it!
    CRM_Utils_Hook::post($hook, get_class($instance), $instance->id, $instance);
    return $instance;
  }

  /**
   * Return an object as an array (used by API functions).
   * @param object $record Object
   * @return array Array
   */
  public static function recordToArray(&$record) {
    $row = [];
    CRM_Core_DAO::storeValues($record, $row);
    $row['resolution'] = unserialize($row['resolution']);
    $row['resolution_count'] = (is_array($row['resolution']) ? count($row['resolution']) : 0);
    return $row;
  }

  /**
   * Update matching info for all records that have parent = this.id.
   * Make sure $this is a proper object that contains contact_id, state and resolution.
   * @return mixed Query result
   * @throws CRM_BlendleImport_Exception If called for a record that hasn't been saved yet
   */
  public function updateChildren() {
    if(empty($this->id)) {
      throw new CRM_BlendleImport_Exception('Could not update children: parent object has no id.');
    }

    $tableName = $this->getTableName();
    return CRM_Core_DAO::executeQuery("
      UPDATE {$tableName} target
      JOIN {$tableName} source ON target.parent = source.id
      SET target.contact_id = source.contact_id, target.state = source.state, target.resolution = source.resolution
      WHERE source.id = '" . CRM_Utils_Type::escape($this->id, 'Positive') . "'");
  }

}
