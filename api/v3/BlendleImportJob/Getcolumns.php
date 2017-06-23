<?php
/**
 * BlendleImportJob.Getcolumns API method.
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
 * @throws API_Exception
 */
function civicrm_api3_blendle_import_job_getcolumns($params) {
  $returnValues = [];
  return civicrm_api3_create_success($returnValues, $params, 'BlendleImportJob', 'Getcolumns');
}

/**
 * @param array $spec
 * @return array
 */
function _civicrm_api3_blendle_import_job_getcolumns_spec(&$spec) {
  $spec = CRM_BlendleImport_BAO_ImportJob::fields();
  $spec['id']['api.required'] = TRUE;
}
