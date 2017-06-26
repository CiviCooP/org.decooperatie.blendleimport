<?php

/**
 * Class CRM_BlendleImport_ImportTask_Membership.
 * Import task: create memberships.
 *
 * @author Kevin Levie <kevin.levie@civicoop.org>
 * @package org.decooperatie.blendleimport
 * @license AGPL-3.0
 */
class CRM_BlendleImport_ImportTask_Membership extends CRM_BlendleImport_ImportTask_BaseTask {

  /**
   * Create activities.
   * @return bool Success
   */
  public function run() {
    $this->log('Membership: task starting...');

    if(empty($this->job->add_membership_type)) {
      return TRUE;
    }

    $contactIds = $this->getContactIdsWithoutMembership();
    $this->log('Membership: will create membership for ' . count($contactIds) . ' contacts.');

    foreach($contactIds as $contactId) {
      try {
        // Default membership start date etc should be fine, join date is set from import date
        $ret = civicrm_api3('Membership', 'create', [
          'contact_id'         => $contactId,
          'membership_type_id' => $this->job->add_membership_type,
          'join_date'          => $this->job->import_date,
        ]);
      } catch (\Exception $e) {
        $this->log('ERROR: Membership could not be created for contact id ' . $contactId . ': ' . $e->getMessage(), PEAR_LOG_ERR);
        continue;
      }
      $this->log('Membership: created, id ' . $ret['id'] . ', cid ' . $contactId . '.');
    }

    $this->updateJobStatus();
    $this->log('Membership: task complete!');
    return TRUE;
  }


  /**
   * Get count of memberships to be created.
   */
  public function getCount() {
    if(empty($this->job->add_membership_type)) {
      return 0;
    }

    return count($this->getContactIdsWithoutMembership());
  }

  /**
   * Get an array of contact ids that have no current membership *at all*.
   * @return array Contact IDs
   * @throws CRM_BlendleImport_Exception On API error
   */
  protected function getContactIdsWithoutMembership() {
    $result = civicrm_api3('Membership', 'get', [
      'contact_id' => ['IN' => $this->getContactIds()],
      // 'membership_type_id' => $this->job->add_membership_type,
      'status_id' => ['IN' => ['New', 'Current', 'Grace']],
      'return' => 'contact_id',
      'options' => ['limit' => 0],
    ]);
    if($result['is_error']) {
      throw new CRM_BlendleImport_Exception('Could not fetch membership contact ids.');
    }

    $alreadyHasMembership = array_column($result['values'], 'contact_id');
    $contactIds = array_diff($this->getContactIds(), $alreadyHasMembership);

    return $contactIds;
  }

  /**
   * Update job status if necessary.
   */
  protected function updateJobStatus() {
    if($this->job->status == CRM_BlendleImport_BAO_ImportJob::STATUS_TAGSMEMB) {
      $this->job->setStatus(CRM_BlendleImport_BAO_ImportJob::STATUS_PAYMENTS);
      $this->job->save();
      $this->log('Updated job status.');
    }
  }

}