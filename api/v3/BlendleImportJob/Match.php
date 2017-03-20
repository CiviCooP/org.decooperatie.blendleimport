<?php
/**
 * BlendleImportJob.Match API method.
 *
 * @author Kevin Levie <kevin.levie@civicoop.org>
 * @package org.decooperatie.blendleimport
 * @license AGPL-3.0
 */

/**
 * Match records for one or more jobs.
 * Set rematch_all=TRUE to rescan records that have already been matched.
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_blendle_import_job_match($params) {
  $jobs = CRM_BlendleImport_BAO_ImportJob::getJobs($params);
  $returnValues = [];

  if (!isset($params['rematch_all'])) {
    $params['rematch_all'] = FALSE;
  }

  foreach ($jobs as $job) {
    $contactsMatched = $job->matchRecords($params['rematch_all']);
    $returnValues[] = ['job_id' => $job->id, 'contacts_matched' => $contactsMatched];
  }
  return civicrm_api3_create_success($returnValues, $params, 'BlendleImportJob', 'Match');
}

/**
 * @param array $spec
 * @return array
 */
function _civicrm_api3_blendle_import_job_match_spec(&$spec) {
  $spec = CRM_BlendleImport_BAO_ImportJob::fields();
  $spec['id']['api.required'] = TRUE;
  $spec['rematch_all'] = [
    'name'  => 'rematch_all',
    'type'  => CRM_Utils_Type::T_BOOLEAN,
    'title' => ts('Rematch all?'),
  ];
}
