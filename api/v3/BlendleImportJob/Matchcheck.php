<?php
/**
 * BlendleImportJob.Matchcheck API method.
 *
 * @author Kevin Levie <kevin.levie@civicoop.org>
 * @package org.decooperatie.blendleimport
 * @license AGPL-3.0
 */

/**
 * Check if all records for this job / these jobs have been matched to a contact.
 * Return true and update status if yes, return false otherwise.
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws CiviCRM_API3_Exception
 */
function civicrm_api3_blendle_import_job_matchcheck($params) {
  $jobs = CRM_BlendleImport_BAO_ImportJob::getJobs($params);
  $returnValue = TRUE;

  foreach ($jobs as $job) {
    if (!$job->checkMatchStatus()) {
      $returnValue = FALSE;
    }
  }
  return civicrm_api3_create_success($returnValue, $params, 'BlendleImportJob', 'Matchcheck');
}

/**
 * @param array $spec
 * @return array
 */
function _civicrm_api3_blendle_import_job_matchcheck_spec(&$spec) {
  $spec = CRM_BlendleImport_BAO_ImportJob::fields();
  $spec['id']['api.required'] = TRUE;
}
