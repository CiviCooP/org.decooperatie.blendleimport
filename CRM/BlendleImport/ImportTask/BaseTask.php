<?php

/**
 * Class CRM_BlendleImport_ImportTask_BaseTask.
 *
 * @author Kevin Levie <kevin.levie@civicoop.org>
 * @package org.decooperatie.blendleimport
 * @license AGPL-3.0
 *
 * @method run()
 * @method getCount()
 */
class CRM_BlendleImport_ImportTask_BaseTask {

  /**
   * @var CRM_BlendleImport_BAO_ImportJob $job Job Cache
   */
  protected $job;

  /**
   * @var CRM_BlendleImport_BAO_ImportRecord[] $records Job Records Cache
   */
  protected $records;

  /**
   * @var array $log Log
   */
  protected $log = [];

  /**
   * Task constructor.
   * @param CRM_BlendleImport_BAO_ImportJob $job
   */
  public function __construct(CRM_BlendleImport_BAO_ImportJob $job = NULL) {
    if (!empty($job)) {
      $this->job = $job;
    }
  }

  /**
   * Log a message to task context logger or browser output.
   * @param string $msg Message
   * @param int $priority Priority
   */
  protected function log($msg, $priority = PEAR_LOG_INFO) {
    $this->log[] = [
      'date' => date('Y-m-d H:i:s'),
      'priority' => $priority,
      'message' => $msg,
    ];
    // error_log('DEBUG BlendleImport: ' . $msg);
  }

  /**
   * Get log (returned by API calls).
   * @return array Log
   */
  public function getLog() {
    return $this->log;
  }

  /**
   * Get job.
   * @return CRM_BlendleImport_BAO_ImportJob
   */
  public function getJob() {
    return $this->job;
  }

  /**
   * Get this job's import records.
   * @return CRM_BlendleImport_BAO_ImportRecord[]|array
   */
  public function getRecords() {
    if(empty($this->records)) {
      $this->records = $this->job->getRecords();
    }
    return $this->records;
  }

  /**
   * Get contact ids for all import records.
   * @return array Contact IDs
   */
  public function getContactIds() {
    $records = $this->getRecords();
    $contact_ids = [];
    foreach ($records as $record) {
      if (!empty($record->contact_id) && !in_array($record->contact_id, $contact_ids)) {
        $contact_ids[] = $record->contact_id;
      }
    }
    return $contact_ids;
  }

}