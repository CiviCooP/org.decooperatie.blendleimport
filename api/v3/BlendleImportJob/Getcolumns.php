<?php
/**
 * BlendleImportJob.Getcolumns API method.
 *
 * @author Kevin Levie <kevin.levie@civicoop.org>
 * @package org.decooperatie.blendleimport
 * @license AGPL-3.0
 */

/**
 * Get column mapping info for a single import job.
 * Parse uploaded CSV file and try to create a field mapping if necessary.
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws CiviCRM_API3_Exception
 */
function civicrm_api3_blendle_import_job_getcolumns($params) {
  $jobs = CRM_BlendleImport_BAO_ImportJob::getJobs($params);
  if (count($jobs) == 0) {
    return [];
  }
  $job = array_shift($jobs);

  if (!isset($params['always_reload'])) {
    $params['always_reload'] = FALSE;
  }

  try {
    $job->parseCSVColumns($params['always_reload']);
    $returnValues = $job->getMappingData();
    return civicrm_api3_create_success($returnValues, $params, 'BlendleImportJob', 'Fileparse');

  } catch(\Exception $e) {
    throw new CiviCRM_API3_Exception('File parse error - ' . $e->getMessage(), 'blendleimport_fileparse_error');
  }
}

/**
 * @param array $spec
 * @return array
 */
function _civicrm_api3_blendle_import_job_getcolumns_spec(&$spec) {
  $spec = CRM_BlendleImport_BAO_ImportJob::fields();
  $spec['id']['api.required'] = TRUE;
  $spec['always_reload'] = [
    'name'  => 'always_reload',
    'type'  => CRM_Utils_Type::T_BOOLEAN,
    'title' => ts('Always reinitialize mapping?'),
  ];
}
