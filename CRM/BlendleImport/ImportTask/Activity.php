<?php

/**
 * Class CRM_BlendleImport_ImportTask_Activity.
 * Import task: create activities.
 *
 * @author Kevin Levie <kevin.levie@civicoop.org>
 * @package org.decooperatie.blendleimport
 * @license AGPL-3.0
 */
class CRM_BlendleImport_ImportTask_Activity extends CRM_BlendleImport_ImportTask_BaseTask {

  /**
   * Create activities.
   * @return bool Success
   */
  public function run() {
    $this->log('Activity: task starting...');

    // Get custom field names
    $cf = CRM_Jourcoop_CustomField::instance();
    $apiFieldName = function($fieldName) use($cf) {
      return $cf->getApiFieldName('Blendle_Import', $fieldName);
    };

    // Create activities
    $records = $this->getRecords();
    foreach($records as $record) {

      if(empty($record->contact_id)) {
        $this->log('ERROR: Activity could not be created for record id ' . $record->id . ': NOT MATCHED WITH CONTACT', PEAR_LOG_ERR);
      }

      $activityParams = [
        'activity_type_id' => 'BlendleImport_ArticleData',
        'activity_date_time' => $this->job->import_date,
        'status_id' => 'Completed',
        'subject' => '[Job: ' . $record->job_id . '] ' . substr($record->title, 0, 80) . '...',
        // 'source_contact_id' => 'user_contact_id',
        'target_contact_id' => $record->contact_id,
        $apiFieldName('Import_Job_ID') => $record->job_id,
        $apiFieldName('Product_UID') => $record->product_uid,
        $apiFieldName('Article_Title') => $record->title,
        $apiFieldName('Author') => $record->byline,
        $apiFieldName('Sales_Count') => (int)$record->sales_count,
        $apiFieldName('Premium_Reads') => (int)$record->premium_reads,
        $apiFieldName('Refunded_Count') => (int)$record->refunded_count,
        $apiFieldName('Refunded_Amount') => number_format($record->refunded_amount, 2, '.', ''),
        $apiFieldName('Vmoney_Amount') => number_format($record->vmoney_amount, 2, '.', ''),
        $apiFieldName('Sales_Amount') => number_format($record->approximated_sales_amount_eur, 2, '.', ''),
        $apiFieldName('Revenue_Amount') => number_format($record->approximated_revenue_eur, 2, '.', ''),
        $apiFieldName('Article_Price') => number_format($record->price, 2, '.', ''),
        $apiFieldName('FB_Costs') => number_format($record->fb_costs, 2, '.', ''),
      ];
      $ret = civicrm_api3('Activity', 'create', $activityParams);

      if($ret['is_error']) {
        $this->log('ERROR: Activity could not be created for record id ' . $record->id . ': ' . $ret['error_message'], PEAR_LOG_ERR);
        continue;
      }

      // Log progress (call seems to return no data if output is not plain ASCII)
      $titleForLog = iconv('UTF-8', 'ASCII//TRANSLIT', substr($record->title, 0, 20));
      $this->log('Activity: created, id: ' . $ret['id'] . ', record id: ' . $record->id . ', cid: ' . $record->contact_id . ' (' . $titleForLog . '...).');
    }

    $this->updateJobStatus();
    $this->log('Activity: task complete!');
    return TRUE;
  }

  /**
   * Get count of activities to be created.
   */
  public function getCount() {
    return count($this->getRecords());
  }

  /**
   * Update job status if necessary.
   */
  protected function updateJobStatus() {
    if($this->job->status == CRM_BlendleImport_BAO_ImportJob::STATUS_ACTIVITIES) {
      $newStatus = (($this->job->add_membership_type || $this->job->add_tag) ? CRM_BlendleImport_BAO_ImportJob::STATUS_TAGSMEMB : CRM_BlendleImport_BAO_ImportJob::STATUS_PAYMENTS);
      $this->job->setStatus($newStatus);
      $this->job->save();
      $this->log('Updated job status.');
    }
  }

}