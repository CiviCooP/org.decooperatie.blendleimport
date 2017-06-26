<?php

/**
 * Class CRM_BlendleImport_ImportTask_Tag.
 * Import task: create tags.
 *
 * @author Kevin Levie <kevin.levie@civicoop.org>
 * @package org.decooperatie.blendleimport
 * @license AGPL-3.0
 */
class CRM_BlendleImport_ImportTask_Tag extends CRM_BlendleImport_ImportTask_BaseTask {

  /**
   * Create tags.
   * @return bool Success
   */
  public function run() {
    $this->log('Tag: task starting...');

    if(empty($this->job->add_tag)) {
      return TRUE;
    }

    $contactIds = $this->getContactIdsWithoutTag();
    $this->log('Tag: will add tag for ' . count($contactIds) . ' contacts.');

    foreach($contactIds as $contactId) {
      try {
        $ret = civicrm_api3('EntityTag', 'create', [
          'entity_table' => 'civicrm_contact',
          'entity_id'    => $contactId,
          'tag_id'       => $this->job->add_tag,
        ]);
      } catch(\Exception $e) {
        $this->log('ERROR: Tag could not be added for contact id ' . $contactId . ': ' . $e->getMessage(), PEAR_LOG_ERR);
        continue;
      }
      $this->log('Tag: added ' . $ret['added'] . ', cid ' . $contactId . '.');
    }

    $this->updateJobStatus();
    $this->log('Tag: task complete!');
    return TRUE;
  }

  /**
   * Get count of tags to be created.
   */
  public function getCount() {
    if(empty($this->job->add_tag)) {
      return 0;
    }

    return count($this->getContactIdsWithoutTag());
  }

  /**
   * Get an array of contact ids for which the tag has not been added.
   * @return array Contact IDs
   * @throws CRM_BlendleImport_Exception On API error
   */
  protected function getContactIdsWithoutTag() {
    $result = civicrm_api3('EntityTag', 'get', [
      'contact_id' => ['IN' => $this->getContactIds()],
      'tag_id' => $this->job->add_tag,
      'return' => 'contact_id',
      'options' => ['limit' => 0],
    ]);
    if($result['is_error']) {
      throw new CRM_BlendleImport_Exception('Could not fetch contact ids without tag.');
    }

    $alreadyHasTag = array_column($result['values'], 'entity_id');
    $contactIds = array_diff($this->getContactIds(), $alreadyHasTag);

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