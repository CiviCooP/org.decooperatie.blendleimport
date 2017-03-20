<?php
/**
 * BlendleImportJob.Import API method.
 *
 * @author Kevin Levie <kevin.levie@civicoop.org>
 * @package org.decooperatie.blendleimport
 * @license AGPL-3.0
 */

/**
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws CiviCRM_API3_Exception
 */
function civicrm_api3_blendle_import_job_import($params) {

    $jobs = CRM_BlendleImport_BAO_ImportJob::getJobs($params);
    if (count($jobs) == 0) {
      return [];
    }
    $job = array_shift($jobs);

    $className = 'CRM_BlendleImport_ImportTask_' . ucfirst($params['task']);
    if (empty($params['task']) || !class_exists($className)) {
      throw new CiviCRM_API3_Exception('Invalid import task name: ' . $params['task'], 'blendleimport_import_task_invalid');
    }

    // Run task
    /** @var CRM_BlendleImport_ImportTask_BaseTask $task */
    $task = new $className($job);
    if ($task->run()) {
      $returnValues = $task->getLog();
    } else {
      throw new CiviCRM_API3_Exception('Import task returned false: ' . $params['task'], 'blendleimport_import_task_failed');
    }

    return civicrm_api3_create_success($returnValues, $params, 'BlendleImportJob', 'Import');
}

/**
 * @param array $spec
 * @return array
 */
function _civicrm_api3_blendle_import_job_import_spec(&$spec) {
  $spec = CRM_BlendleImport_BAO_ImportJob::fields();
  $spec['id']['api.required'] = TRUE;
  $spec['task'] = [
    'name'         => 'task',
    'type'         => CRM_Utils_Type::T_STRING,
    'title'        => ts('Import Task'),
    'required'     => TRUE,
    'api.required' => TRUE,
  ];
}
