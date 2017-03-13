<?php
/**
 * BlendleImportRecord.Get API method.
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
function civicrm_api3_blendle_import_record_get($params) {
  $returnValues = CRM_BlendleImport_BAO_ImportRecord::getRecords($params, TRUE);
  return civicrm_api3_create_success($returnValues, $params, 'BlendleImportRecord', 'Get');
}

/**
 * @param array $spec
 * @return array
 */
function _civicrm_api3_blendle_import_record_get_spec(&$spec) {
  $spec = CRM_BlendleImport_BAO_ImportRecord::fields();
  $spec['job_id']['api.required'] = TRUE;
  $spec['is_unique']['api.default'] = TRUE;
}
