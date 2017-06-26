<?php
/**
 * BlendleImportJob.Fileparse API method.
 *
 * @author Kevin Levie <kevin.levie@civicoop.org>
 * @package org.decooperatie.blendleimport
 * @license AGPL-3.0
 */

/**
 * Parse uploaded CSV file for a single import job and load it into the records table.
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws CiviCRM_API3_Exception
 */
function civicrm_api3_blendle_import_job_fileload($params) {
  if(!empty($params['mapping'])) {
    $mapping = $params['mapping'];
    unset($params['mapping']);
  }

  $jobs = CRM_BlendleImport_BAO_ImportJob::getJobs($params);
  if (count($jobs) == 0) {
    return [];
  }
  $job = array_shift($jobs);

  try {
    if(!empty($mapping)) {
      $job->setMapping($mapping);
      $job->save();
    }

    $ret = $job->parseCSVData();
    $returnValues = ['job_id' => $job->id, 'result' => $ret];

    return civicrm_api3_create_success($returnValues, $params, 'BlendleImportJob', 'Fileload');

  } catch(\Exception $e) {
    throw new CiviCRM_API3_Exception('File load error - ' . $e->getMessage(), 'blendleimport_fileload_error');
  }
}

/**
 * @param array $spec
 * @return array
 */
function _civicrm_api3_blendle_import_job_fileload_spec(&$spec) {
  $spec = CRM_BlendleImport_BAO_ImportJob::fields();
  $spec['id']['api.required'] = TRUE;
  $spec['mapping'] = [
    'name'         => 'mapping',
    'type'         => CRM_Utils_Type::T_STRING,
    'title'        => ts('Updated field mapping'),
    'required'     => FALSE,
  ];
}
