<?php

/**
 * Class CRM_BlendleImport_Utils.
 *
 * @author Kevin Levie <kevin.levie@civicoop.org>
 * @package org.decooperatie.blendleimport
 * @license AGPL-3.0
 */
class CRM_BlendleImport_Utils {

  /** @var float Plugin version, used below to check if loadConfigFromJson needs to run. */
  private static $plugin_version = 1.2;

  /**
   * @var string Setting name where we store if config items have already been imported.
   */
  private static $jsonSettingName = 'org.decooperatie.blendleimport.jsonLoaded';

  /**
   * Import activities and custom fields for this module from /json/configitems/.
   * @return bool Success
   * @throws CRM_BlendleImport_Exception If directory not found or org.civicoop.configitems not enabled
   */
  public static function loadConfigFromJson() {

    // Check if this function has already run (note: this is >= 4.7 only syntax!)
    $configLoaded = Civi::settings()->get(static::$jsonSettingName);

    if (!isset($configLoaded) || $configLoaded == false || $configLoaded < static::$plugin_version) {

      $jsonPath = realpath(__DIR__ . '/../../json/configitems/');

      if (!$jsonPath) {
        throw new CRM_BlendleImport_Exception('Cannot load JSON config items: directory not found.');
      }

      if (!class_exists('CRM_Civiconfig_Loader')) {
        throw new CRM_BlendleImport_Exception('Could not load JSON config items: module org.civicoop.configitems is not enabled!');
      }

      // Call loader
      $loader = new CRM_Civiconfig_Loader;
      $result = $loader->updateConfigurationFromJson($jsonPath);

      // Set configLoaded = version, and show status message with result
      Civi::settings()->set(static::$jsonSettingName, static::$plugin_version);

      CRM_Core_Session::setStatus(ts('Added custom data and config for Blendle Import extension.'));
      // . "\n\nDebug output:\n" . nl2br(print_r($result, TRUE)) . "\n");
    }

    return TRUE;
  }

  /**
   * Add menu item for this extension.
   * @param array $menu Menu
   */
  public static function addMenuItem(&$menu) {
    _blendleimport_civix_insert_navigation_menu($menu, 'Contacts', [
      'label'      => ts('Blendle Import'),
      'name'       => 'blendleimport',
      'url'        => 'civicrm/blendleimport',
      'permission' => 'edit all contacts,import contacts',
      'operator'   => 'AND',
      'separator'  => 0,
    ]);
    _blendleimport_civix_navigationMenu($menu);
  }

}
