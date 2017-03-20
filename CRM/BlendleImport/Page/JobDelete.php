<?php

/**
 * Class CRM_BlendleImport_Page_JobDelete.
 * Delete a single import job. Does not ask for confirmation, this happens on the list page.
 *
 * @author Kevin Levie <kevin.levie@civicoop.org>
 * @package org.decooperatie.blendleimport
 * @license AGPL-3.0
 */
class CRM_BlendleImport_Page_JobDelete extends CRM_Core_Page {

  /**
   * Display page.
   */
  public function run() {
    CRM_Utils_System::setTitle(ts('Delete Import Job'));

    $jobId = CRM_Utils_Request::retrieve('id', 'Positive');

    $job = CRM_BlendleImport_BAO_ImportJob::getJobById($jobId);
    if(!$job) {
      throw new CRM_BlendleImport_Exception('Error: could not fetch job with id ' . (int)$jobId . '.');
    }

    $job->delete();

    CRM_Core_Session::setStatus(ts('Import job %1 deleted.', [1 => $jobId]), '', 'success');
    CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/blendleimport'));
  }

}