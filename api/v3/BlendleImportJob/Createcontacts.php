<?php
/**
 * BlendleImportJob.CreateContacts API method.
 *
 * @author Kevin Levie <kevin.levie@civicoop.org>
 * @package org.decooperatie.blendleimport
 * @license AGPL-3.0
 */

/**
 * Create contacts for all records for one or more jobs.
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws CiviCRM_API3_Exception
 */
function civicrm_api3_blendle_import_job_createcontacts($params) {
  $jobs = CRM_BlendleImport_BAO_ImportJob::getJobs($params);
  $returnValues = [];

  foreach ($jobs as $job) {
    $contactsCreated = $job->createContacts();
    $returnValues[] = ['job_id' => $job->id, 'contacts_created' => $contactsCreated];
  }
  return civicrm_api3_create_success($returnValues, $params, 'BlendleImportJob', 'Match');
}

/**
 * @param array $spec
 * @return array
 */
function _civicrm_api3_blendle_import_job_createcontacts_spec(&$spec) {
  $spec = CRM_BlendleImport_BAO_ImportJob::fields();
  $spec['id']['api.required'] = TRUE;
}
