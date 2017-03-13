<?php
/**
 * BlendleImportRecord.Create API method.
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
function civicrm_api3_blendle_import_record_create($params) {
  $record = CRM_BlendleImport_BAO_ImportRecord::create($params, TRUE);
  $returnValues = CRM_BlendleImport_BAO_ImportRecord::recordToArray($record);
  return civicrm_api3_create_success($returnValues, $params, 'BlendleImportRecord', 'Create');
}

/**
 * @param array $spec
 * @return array
 */
function _civicrm_api3_blendle_import_job_record_spec(&$spec) {
  $spec = CRM_BlendleImport_BAO_ImportRecord::fields();
  $spec['update_all']['api.default'] = TRUE;
}
