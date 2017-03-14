<?php

/**
 * Class CRM_BlendleImport_BAO_ImportJob.
 * BAO for table civicrm_blendleimport_job.
 *
 * @author Kevin Levie <kevin.levie@civicoop.org>
 * @package org.decooperatie.blendleimport
 * @license AGPL-3.0
 */
class CRM_BlendleImport_BAO_ImportJob extends CRM_BlendleImport_DAO_ImportJob {

  /**
   * Fetch an array of Import Job objects.
   * @param array $params Input parameters to find object(s).
   * @param bool $asArray Whether to return an array of arrays instead of objects.
   * @return static[]|null The found object(s) or null
   */
  public static function getJobs($params = [], $asArray = FALSE) {
    $result = [];
    $instance = new static;

    // Quick hack: not sure how to best support IS NULL, added manually this way
    foreach($params as $paramName => &$param) {
      if(is_array($param) && isset($param['IS NULL'])) {
        $instance->whereAdd(CRM_Utils_Type::escape($paramName, 'String') . ' IS NULL');
        unset($params[$paramName]);
      }
    }

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
   * Create / update an import job based on array-data.
   * @param array $params key-value pairs
   * @param bool $returnObject Return full created object?
   * @return static|mixed Create result
   */
  public static function create($params, $returnObject = FALSE) {
    $instance = new static;

    $hook = empty($params['id']) ? 'create' : 'edit';
    CRM_Utils_Hook::pre($hook, get_class($instance), CRM_Utils_Array::value('id', $params), $params);

    // Set created date / id when we're creating a new job
    if (empty($params['id'])) {
      $params['status'] = static::STATUS_NEW;
      $params['created_date'] = date('Y-m-d H:i:s');
      $params['created_id'] = CRM_Core_Session::getLoggedInContactID();
    }

    $instance->copyValues($params);
    $instance->save();

    // Handle CSV upload if csv_upload contains data (= base64 encoded CSV) + try to match records
    if (!empty($params['csv_upload'])) {
      $instance->storeCSVData($params['csv_upload'], TRUE);
      $instance->setStatus(static::STATUS_CONTACTS);
      $instance->save();
      $instance->matchRecords();
    }

    CRM_Utils_Hook::post($hook, get_class($instance), $instance->id, $instance);

    // What is the best way to reload + return object?
    if($returnObject) {
      return $instance->findById($instance->id);
    } else {
      return $instance;
    }
  }

  /**
   * Set import job status. See STATUS_* contants in the DAO.
   * @param int $status_id Status ID
   * @throws CRM_BlendleImport_Exception If status ID is invalid
   */
  public function setStatus($status_id) {
    if (!in_array($status_id, [self::STATUS_NEW, self::STATUS_CONTACTS, self::STATUS_ACTIVITIES, self::STATUS_CONTRIBUTIONS, self::STATUS_COMPLETE])) {
      throw new CRM_BlendleImport_Exception('Invalid value for civicrm_blendleimport_job.status: ' . $status_id . '.');
    }

    $this->status = $status_id;
  }

  /**
   * Get import job status.
   * @return string
   */
  public function getStatus() {
    return $this->status;
  }

  /**
   * Get import job status description.
   * @return string
   */
  public function getStatusDescription() {
    switch ($this->status) {
      case self::STATUS_NEW:
        return ts('New');
        break;
      case self::STATUS_CONTACTS:
        return ts('Match Contacts');
        break;
      case self::STATUS_ACTIVITIES:
        return ts('Import Articles');
        break;
      case self::STATUS_CONTRIBUTIONS:
        return ts('Generate Payments');
        break;
      case self::STATUS_COMPLETE:
        return ts('Complete');
        break;
      default:
        return ts('Unknown');
        break;
    }
  }

  /**
   * Store CSV data to file and store the file name.
   * This does not save the object - call save() manually!
   * @param mixed $data CSV data
   * @param bool $isBase64Encoded Whether data is base64 œencoded
   * @return bool Success
   */
  public function storeCSVData($data, $isBase64Encoded = TRUE) {
    $csvfile = new CRM_BlendleImport_Import_CSVReader($this->id);
    return $csvfile->writeToTable($data, $isBase64Encoded);
  }

  /**
   * Get records for this import job.
   * @param bool $asArray Return an array instead of objects?
   * @return array Import Records
   */
  public function getRecords($asArray = FALSE) {
    return CRM_BlendleImport_BAO_ImportRecord::getRecords(['job_id' => $this->id], $asArray);
  }

  /**
   * Check if this job has import_records.
   * @return int Record Count
   */
  public function getRecordCount() {
    return CRM_BlendleImport_BAO_ImportRecord::getRecordCount(['job_id' => $this->id]);
  }

  /**
   * Call MatchFinder for this job's import_records.
   * @param bool $rematchAll Rescan existing matches?
   * @return int Number of records matched
   */
  public function matchRecords($rematchAll = FALSE) {

    $params = ['job_id' => $this->id];
    if(!$rematchAll) {
      $params['parent'] = ['IS NULL' => TRUE];
      $params['contact_id'] = ['IS NULL' => TRUE];
    }

    $records = CRM_BlendleImport_BAO_ImportRecord::getRecords($params);
    $mf = CRM_BlendleImport_Import_MatchFinder::instance();

    foreach($records as &$record) {
      $mf->match($record);
    }

    return count($records);
  }

  /**
   * Check if all records have been matched and update job status if that is the case.
   * @return bool All records matched?
   */
  public function checkMatchStatus() {

    $count = CRM_BlendleImport_BAO_ImportRecord::getRecordCount([
      'job_id' => $this->id,
      'contact_id' => ['IS NULL' => TRUE],
    ]);

    if($count == 0 && $this->status == static::STATUS_CONTACTS) {
      $this->setStatus(static::STATUS_ACTIVITIES);
      $this->save();
    }

    return ($count == 0);
  }

  /**
   * Create new contacts for this job's import_records where necessary.
   * @return int Number of contacts created
   */
  public function createContacts() {

    $records = CRM_BlendleImport_BAO_ImportRecord::getRecords([
      'job_id' => $this->id,
      'contact_id' => ['IS NULL' => TRUE],
      'state' => 'impossible',
    ]);
    $createdCache = [];

    foreach($records as &$record) {

      if(array_key_exists($record->byline, $createdCache)) {

        // Contact already created, use id from cache
        $record->contact_id = $createdCache[$record->byline];
      } else {

        // Clean up name and try to create contact
        $names = CRM_BlendleImport_Import_MatchFinder::cleanupName($record->byline);
        if (!$names) {
          continue;
        }

        $contact = civicrm_api3('Contact', 'create', [
          'contact_type' => 'Individual',
          'first_name'   => $names['first'],
          'last_name'    => $names['last'],
        ]);
        if(empty($contact['id'])) {
          continue;
        }

        $record->contact_id = $contact['id'];
        $record->state = 'found';

        $createdCache[$record->byline] = $contact['id'];
      }

      // Save updated record(s)
      $record->save();
      $record->updateChildren();
    }

    return count($createdCache);
  }

  /**
   * Return an object as an array (used by API functions).
   * @param object $record Object
   * @return array Array
   */
  public static function recordToArray(&$record) {
    $row = [];
    CRM_Core_DAO::storeValues($record, $row);
    $row['record_count'] = $record->getRecordCount();
    return $row;
  }

}
