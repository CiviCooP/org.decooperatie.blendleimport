<?php
/**
 * BlendleImportJob.Importcount API method.
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
function civicrm_api3_blendle_import_job_importcount($params) {
  $jobs = CRM_BlendleImport_BAO_ImportJob::getJobs($params);
  if (count($jobs) == 0) {
    return [];
  }

  $job = array_shift($jobs);
  $returnValues = [
    'job_id'           => $job->id,
    'activity_count'   => (new CRM_BlendleImport_ImportTask_Activity($job, null))->getCount(),
    'tag_count'        => (new CRM_BlendleImport_ImportTask_Tag($job, null))->getCount(),
    'membership_count' => (new CRM_BlendleImport_ImportTask_Membership($job, null))->getCount(),
    'payment_count' => (new CRM_BlendleImport_ImportTask_Payment($job, null))->getCount(),
  ];

  return civicrm_api3_create_success($returnValues, $params, 'BlendleImportJob', 'Importcount');
}

/**
 * @param array $spec
 * @return array
 */
function _civicrm_api3_blendle_import_job_importcount_spec(&$spec) {
  $spec = CRM_BlendleImport_BAO_ImportJob::fields();
}
