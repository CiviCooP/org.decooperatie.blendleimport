<?php
/**
 * BlendleImportJob.Get API method.
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
function civicrm_api3_blendle_import_job_get($params) {
  $returnValues = CRM_BlendleImport_BAO_ImportJob::getJobs($params, TRUE);
  return civicrm_api3_create_success($returnValues, $params, 'BlendleImportJob', 'Get');
}

/**
 * @param array $spec
 * @return array
 */
function _civicrm_api3_blendle_import_job_get_spec(&$spec) {
  $spec = CRM_BlendleImport_BAO_ImportJob::fields();
}
