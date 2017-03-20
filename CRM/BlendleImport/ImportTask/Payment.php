<?php

/**
 * Class CRM_BlendleImport_ImportTask_Payment.
 * Import task: generate payments.
 *
 * @author Kevin Levie <kevin.levie@civicoop.org>
 * @package org.decooperatie.blendleimport
 * @license AGPL-3.0
 */
class CRM_BlendleImport_ImportTask_Payment extends CRM_BlendleImport_ImportTask_BaseTask {

  /**
   * Generate payments.
   * @return bool Success
   */
  public function run() {
    $this->log('Payment: task starting...');

    $cf = CRM_Jourcoop_CustomField::instance();
    $contactIds = $this->getContactIds();
    foreach($contactIds as $contactId) {

      // Fetch activities for this contact for this import job
      $ret = civicrm_api3('Activity', 'get', [
          'target_contact_id' => $contactId,
          'activity_type_id' => 'BlendleImport_ArticleData',
          $cf->getApiFieldName('Blendle_Import', 'Import_Job_ID') => $this->job->id,
          'options' => ['limit' => 0],
      ]);
      if($ret['is_error']) {
        $this->log('ERROR: Payment task could not fetch activities for contact id ' . $contactId . ': ' . $ret['error_message'], PEAR_LOG_ERR);
        continue;
      }
      $activities = &$ret['values'];

      // Check if the same number of records exists in blendleimport_records
      $recordCount = CRM_BlendleImport_BAO_ImportRecord::getRecordCount([
        'job_id' => $this->job->id,
        'contact_id' => $contactId,
      ]);
      if(!$recordCount || $recordCount != count($activities)) {
        $this->log('ERROR: Payment not created, number of activities (' . count($activities) . ') does not match import records (' . $recordCount . ') for contact id ' . $contactId . '!', PEAR_LOG_ERR);
        continue;
      }

      // Calculate total revenue
      $revenueFieldId = $cf->getApiFieldName('Blendle_Import', 'Revenue_Amount');
      $revenue = 0;
      foreach($activities as $activity) {
          $revenue += (double)$activity[$revenueFieldId];
      }

      // Create contribution
      $totalAmount = number_format(-$revenue, 2, '.', '');
      $contributionParams = [
        'financial_type_id' => 'Royalties',
        'total_amount' => $totalAmount,
        'contact_id' => $contactId,
        'payment_instrument_id' => 'Handled by Exact',
        'receive_date' => $this->job->import_date,
        'source' => 'Blendle Import (job: ' . $this->job->id . ')',
      ];
      $cret = civicrm_api3('Contribution', 'create', $contributionParams);

      if($cret['is_error']) {
        $this->log('ERROR: Payment task could not create contribution for contact id ' . $contactId . ': ' . $ret['error_message'], PEAR_LOG_ERR);
        continue;
      }

      $this->log('Payment: created, id ' . $cret['id'] . ', cid: ' . $contactId . ', activity count: ' . count($activities) . ', total amount: ' . $totalAmount . '.');
    }

    $this->updateJobStatus();
    $this->log('Payment: task complete!');
    return TRUE;
  }

  /**
   * Get count of payments to be created.
   */
  public function getCount() {
    return count($this->getContactIds());
  }

  /**
   * Update job status if necessary.
   */
  protected function updateJobStatus() {
    if($this->job->status == CRM_BlendleImport_BAO_ImportJob::STATUS_PAYMENTS) {
      $this->job->setStatus(CRM_BlendleImport_BAO_ImportJob::STATUS_COMPLETE);
      $this->job->save();
      $this->log('Updated job status.');
    }
  }

}