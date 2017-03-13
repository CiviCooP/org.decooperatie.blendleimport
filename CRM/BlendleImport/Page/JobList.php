<?php

/**
 * Class CRM_BlendleImport_Page_JobList.
 * Main page: lists existing import jobs and allows starting a new import.
 *
 * @author Kevin Levie <kevin.levie@civicoop.org>
 * @package org.decooperatie.blendleimport
 * @license AGPL-3.0
 */
class CRM_BlendleImport_Page_JobList extends CRM_Core_Page {

  /**
   * Display page.
   */
  public function run() {

    CRM_Utils_System::setTitle(ts('Blendle Import'));

    $jobs = CRM_BlendleImport_BAO_ImportJob::getJobs();
    $this->assign('rows', $jobs);

    parent::run();
  }

}
