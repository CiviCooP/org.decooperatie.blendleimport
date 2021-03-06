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
   * Fetch Import Record rows.
   * @param array $params Input parameters to find object(s).
   * @param string $returnFormat Whether to return 'object', 'array' or 'count'.
   * @return static[]|int|null The found object(s), count or null
   */
  public static function getRecords($params = [], $returnFormat = 'object') {
    $result = [];
    $instance = new static;

    // Quick hack: not sure how to best support IS NULL, added manually this way
    foreach ($params as $paramName => $param) {
      if (is_array($param) && isset($param['IS NULL'])) {
        $instance->whereAdd(CRM_Utils_Type::escape($paramName, 'String') . ' IS NULL');
        unset($params[$paramName]);
      }
    }

    // Find and fetch items
    if (!empty($params)) {
      $instance->copyValues($params);
    }

    // Return count?
    if($returnFormat == 'count') {
      return $instance->count();
    }

    // Return records as an array of arrays/objects
    $instance->find();
    while ($instance->fetch()) {
      if ($returnFormat == 'array') {
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
    return static::getRecords($params, 'count');
  }

  /**
   * Clear import records for a certain import job.
   * @param int $job_id Job ID
   * @return bool Success
   */
  public static function clearRecordsForJob($job_id) {
    $record = new static;
    $record->whereAdd('job_id = ' . (int) $job_id);
    return $record->delete(TRUE);
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
    if (isset($params['state']) && !in_array($params['state'], ['chosen', 'multiple', 'impossible'])) {
      throw new CRM_BlendleImport_Exception('Invalid value for state: ' . $params['state'] . '.');
    }
    if ($params['contact_id'] == 0) {
      $params['contact_id'] = NULL;
    }

    // Update this row
    if (!empty($params['resolution']) && is_array($params['resolution'])) {
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
    if (empty($this->id)) {
      throw new CRM_BlendleImport_Exception('Could not update children: parent object has no id.');
    }

    $tableName = $this->getTableName();
    return CRM_Core_DAO::executeQuery("
          UPDATE {$tableName} target
          JOIN {$tableName} source ON target.parent = source.id
          SET target.contact_id = source.contact_id, target.state = source.state, target.resolution = source.resolution
          WHERE source.id = '" . CRM_Utils_Type::escape($this->id, 'Positive') . "'
        ");
  }

}