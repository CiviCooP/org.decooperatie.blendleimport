<?php

/**
 * Class CRM_BlendleImport_Upgrader.
 * Collection of upgrade steps.
 *
 * @author Kevin Levie <kevin.levie@civicoop.org>
 * @package org.decooperatie.blendleimport
 * @license AGPL-3.0
 */
class CRM_BlendleImport_Upgrader extends CRM_BlendleImport_Upgrader_Base {

  /**
   * On extension install...
   * @return bool Success
   */
  public function install() {

    // Create tables
    $this->executeSqlFile('sql/table_job.sql');
    $this->executeSqlFile('sql/table_records.sql');

    // Run config items loader
    CRM_BlendleImport_Utils::loadConfigFromJson();

    return true;
  }

  /**
   * On extension uninstall...
   * @return bool Success
   */
  public function uninstall() {

    // Drop tables
    $this->executeSqlFile('sql/uninstall.sql');

    return true;
  }

  /**
   * On upgrade to 1.2.0...
   * @return bool Success
   */
  public function upgrade_1001() {

    // Add database column
    $this->executeSqlFile('sql/upgrade_1001.sql');

    // Load updated custom fields
    CRM_BlendleImport_Utils::loadConfigFromJson();

    return true;
  }

}
