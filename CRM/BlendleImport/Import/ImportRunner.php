<?php

/**
 * Class CRM_BlendleImport_Import_ImportRunner.
 * Import runner - queues and calls ImportTasks.
 * NOT IN USE ANYMORE.
 *
 * @author Kevin Levie <kevin.levie@civicoop.org>
 * @package org.decooperatie.blendleimport
 * @license AGPL-3.0
 */

/*
class CRM_BlendleImport_Import_ImportRunner {

  /**
   * Setup queue. Called from Page_ImportRunner.
   * Does not check if job or task classes exist at this time.
   * @param int $jobId Job ID
   * @param array $tasks Task Names
   * @return CRM_Queue_Queue Queue
   *
  public static function setupQueue($jobId, $tasks = []) {

    $queue = CRM_Queue_Service::singleton()->create([
      'type' => 'Sql',
      'name' => 'org.decooperatie.blendleimport',
      'reset' => TRUE,
    ]);

    foreach($tasks as $taskName) {
      $task = new CRM_Queue_Task(
        ['CRM_BlendleImport_Import_ImportRunner', 'runTask'],
        [$jobId, $taskName],
        "Running " . ucfirst($taskName) . " Task...");
      $queue->createItem($task);
    }

    return $queue;
  }

  /**
   * Run a single task. Called by QueueRunner.
   * Invokes an ImportTask_TaskName class.
   * @param CRM_Queue_TaskContext $taskContext Task Context
   * @param int $jobId Job ID
   * @param string $taskName Task Name
   * @return bool Success
   * @throws CRM_BlendleImport_Exception If job or task class does not exist
   *
  public static function runTask(CRM_Queue_TaskContext $taskContext, $jobId, $taskName) {

    // Verify input
    $job = CRM_BlendleImport_BAO_ImportJob::getJobById($jobId);
    if(!$job) {
      throw new CRM_BlendleImport_Exception('Invalid job id: ' . (int)$jobId);
    }

    $className = 'CRM_BlendleImport_ImportTask_' . ucfirst($taskName);
    if (!class_exists($className)) {
      throw new CRM_BlendleImport_Exception('Invalid import task name: ' . $taskName);
    }

    // Run task
    /** @var CRM_BlendleImport_ImportTask_BaseTask $task *
    $task = new $className($job, $taskContext);
    $result = $task->run();

    return $result;
  }

  /**
   * On queue runner finish... 'Serialization of closure is not allowed.'
   *
  public static function onQueueEnd() {
    CRM_Core_Session::setStatus(ts('Import tasks completed. Search for contacts, activities and contributions to check the import results.'), '', 'success');
  }

}
*/

/**
 * Class CRM_BlendleImport_Page_ImportRunner.
 * Trigger import task and show task queue progress.
 * NOT IN USE ANYMORE.
 *
 * @author Kevin Levie <kevin.levie@civicoop.org>
 * @package org.decooperatie.blendleimport
 * @license AGPL-3.0
 */

/*
class CRM_BlendleImport_Page_ImportRunner extends CRM_Core_Page {

  // Initialise task queue and call runner for one or more import tasks.
  public function run() {

    // Parse query parameters

    $jobId = CRM_Utils_Request::retrieve('id', 'Positive');
    $tasks = CRM_Utils_Request::retrieve('tasks', 'String');

    $job = CRM_BlendleImport_BAO_ImportJob::getJobById($jobId);
    if(!$job) {
      throw new CRM_BlendleImport_Exception('ImportRunner error: could not fetch job with id ' . (int)$jobId . '.');
    }

    $tasks = explode('-', $tasks);
    if(!$tasks || !is_array($tasks) || count($tasks) == 0) {
      throw new CRM_BlendleImport_Exception('ImportRunner error: invalid or no task list supplied.');
    }

    // Setup queue and run all tasks

    $queue = CRM_BlendleImport_Import_ImportRunner::setupQueue($job->id, $tasks);

    $runner = new CRM_Queue_Runner([
      'title' => 'Blendle Import: job ' . $job->id . ' (' . $job->name . ')',
      'queue' => $queue,
      'errorMode' => CRM_Queue_Runner::ERROR_ABORT,
      'onEnd' => ['CRM_BlendleImport_Import_ImportRunner', 'onQueueEnd'],
      'onEndUrl' => CRM_Utils_System::url('civicrm/blendleimport', ['reset' => 1], TRUE),
    ]);

    $runner->runAllViaWeb();
    exit;
  }

}
*/
