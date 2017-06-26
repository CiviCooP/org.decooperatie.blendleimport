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
   * Fetch an array of import jobs.
   * @param array $params Input parameters to find object(s).
   * @param bool $asArray Whether to return an array of arrays instead of objects.
   * @return static[]|null The found object(s) or null
   */
  public static function getJobs($params = [], $asArray = FALSE) {
    $result = [];
    $instance = new static;

    // Quick hack: not sure how to best support IS NULL, added manually this way
    foreach ($params as $paramName => &$param) {
      if (is_array($param) && isset($param['IS NULL'])) {
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
   * Get a single import job by ID.
   * @param int $jobId Job ID
   * @return static|null The found object or null
   */
  public static function getJobById($jobId) {
    $instance = new static;
    $instance->id = $jobId;
    $instance->find(TRUE);
    return $instance;
  }

  /**
   * Create / update an import job based on array-data.
   * @param array $params key-value pairs
   * @param bool $returnObject Return full created object?
   * @return static|mixed Create result
   * @throws CRM_BlendleImport_Exception If CSV data has been uploaded and cannot be parsed
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

    if(!empty($params['mapping'])) {
      $params['mapping'] = serialize($params['mapping']);
    }

    if(!empty($params['data'])) {
      if (preg_match('@^data:[^;]*;base64,@', $params['data'], $matches)) {
        $params['data'] = base64_decode(substr($params['data'], strlen($matches[0])), TRUE);
        if ($params['data'] === FALSE) {
          throw new CRM_BlendleImport_Exception('Could not decode base64 encoded data: ' . htmlspecialchars(substr($params['data'], 0, 20)));
        }
      }
      $params['mapping'] = '';
    }

    $instance->copyValues($params);
    $instance->save();

    CRM_Utils_Hook::post($hook, get_class($instance), $instance->id, $instance);

    // What is the best way to reload + return object?
    if ($returnObject) {
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
    if (!in_array($status_id, [self::STATUS_NEW, self::STATUS_PARSEFILE, self::STATUS_CONTACTS, self::STATUS_ACTIVITIES, self::STATUS_TAGSMEMB, self::STATUS_PAYMENTS, self::STATUS_COMPLETE])) {
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
      case self::STATUS_PARSEFILE:
        return ts('Parse File');
        break;
      case self::STATUS_CONTACTS:
        return ts('Match Contacts');
        break;
      case self::STATUS_ACTIVITIES:
        return ts('Create Activities');
        break;
      case self::STATUS_TAGSMEMB:
        return ts('Create Tags/Memberships');
        break;
      case self::STATUS_PAYMENTS:
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
   * Read CSV file from $this->data and try to fetch and match column headers.
   * If successful, set the job's status to STATUS_PARSEFILE.
   * @param bool $alwaysReload Always reload, even if $this->mapping is not empty
   * @return bool Success
   * @throws CRM_BlendleImport_Exception If no data or column header found
   */
  public function parseCSVColumns($alwaysReload = FALSE) {
    if(!empty($this->mapping) && $alwaysReload === FALSE) {
      return FALSE;
    }

    $csvfile = new CRM_BlendleImport_Import_CSVReader($this->id);
    $ret = $csvfile->parseColumns($this->data);

    if (!$ret) {
      throw new CRM_BlendleImport_Exception('Cannot process CSV columns: resulting mapping is empty.');
    }

    $this->setMapping($ret['mapping'], $ret['columns']);
    $this->setStatus(static::STATUS_PARSEFILE);
    $this->save();
    return TRUE;
  }

  /**
   * Return an array of mappable fields + name + title + currently selected CSV column header.
   * @return array
   * @throws CRM_BlendleImport_Exception If called before parseCSVColumns()
   */
  public function getMappingData() {
    if(empty($this->mapping) || !($mapping = $this->getMapping())) {
      throw new CRM_BlendleImport_Exception('Cannot return mapping data: mapping field is empty.');
    }

    $validFields = CRM_BlendleImport_BAO_ImportRecord::fields();
    $ret = [];

    foreach($mapping['mapping'] as $mFieldName => $mCSVColumnName) {
      $ret[] = [
        'name' => $validFields[$mFieldName]['name'],
        'title' => $validFields[$mFieldName]['title'],
        'required' => ($validFields[$mFieldName]['mapping_required'] ? TRUE : FALSE),
        'mapping' => $mCSVColumnName,
        'mappingOptions' => $mapping['columns'],
      ];
    }

    return $ret;
  }

  /**
   * Parse CSV data in $this->data based on the column mapping in $this->mapping.
   * If succesful, set the job's status to STATUS_CONTACTS and try to match contacts.
   * @return bool Success
   * @throws CRM_BlendleImport_Exception If field mapping or data string empty
   */
  public function parseCSVData() {
    $mapping = $this->getMapping();
    if(empty($mapping) || count($mapping) == 0) {
      throw new CRM_BlendleImport_Exception('Cannot process CSV data: incomplete or empty field mapping.');
    }
    if(empty($this->data)) {
      throw new CRM_BlendleImport_Exception('Cannot process CSV data: data field is empty.');
    }

    $csvfile = new CRM_BlendleImport_Import_CSVReader($this->id);
    $ret = $csvfile->writeToTable($this->data, $mapping);
    if(!$ret) {
      throw new CRM_BlendleImport_Exception('Cannot process CSV data: data could not be parsed as CSV.');
    }

    $this->setStatus(static::STATUS_CONTACTS);
    $this->save();
    $this->matchRecords();
    return TRUE;
  }

  /**
   * Get current mapping data as an array.
   * @return array Mapping data
   */
  public function getMapping() {
    $mapping = unserialize($this->mapping);
    if(empty($mapping)) {
      $mapping = [];
    }
    return $mapping;
  }

  /**
   * Set mapping data from an array, keeping column headers intact if they are not supplied.
   * @param array $newMapping Mapping data
   * @param array|null $newColumns Column headers
   */
  public function setMapping($newMapping, $newColumns = null) {
    $mapping = $this->getMapping();
    $mapping['mapping'] = $newMapping;
    if(!empty($newColumns)) {
      $mapping['columns'] = $newColumns;
    }
    $this->mapping = serialize($mapping);
  }

    /**
   * Get records for this import job.
   * @param bool $unique Return unique contacts?
   * @param string $returnFormat Whether to return 'object', 'array' or 'count'.
   * @return array Import Records
   */
  public function getRecords($unique = FALSE, $returnFormat = 'object') {
    $params = ['job_id' => $this->id];
    if ($unique) {
      $params['parent'] = ['IS NULL' => TRUE];
    }
    return CRM_BlendleImport_BAO_ImportRecord::getRecords($params, $returnFormat);
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
    if (!$rematchAll) {
      $params['parent'] = ['IS NULL' => TRUE];
      $params['contact_id'] = ['IS NULL' => TRUE];
    }

    $records = $this->getRecords(TRUE);
    $mf = CRM_BlendleImport_Import_MatchFinder::instance();

    foreach ($records as &$record) {
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
      'job_id'     => $this->id,
      'contact_id' => ['IS NULL' => TRUE],
    ]);

    if ($count == 0 && $this->status == static::STATUS_CONTACTS) {
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
      'job_id'     => $this->id,
      'contact_id' => ['IS NULL' => TRUE],
      'parent'     => ['IS NULL' => TRUE],
      'state'      => 'impossible',
    ]);
    $createdCache = [];

    foreach ($records as &$record) {

      if (array_key_exists($record->byline, $createdCache)) {

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
        if (empty($contact['id'])) {
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
    $row['data_present'] = (!empty($row['data']));
    unset($row['data']);
    unset($row['mapping']);
    $row['record_count'] = $record->getRecordCount();
    return $row;
  }

}