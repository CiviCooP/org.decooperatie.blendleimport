<?php

require_once 'blendleimport.civix.php';


/**
 * Implements hook_civicrm_navigationMenu().
 * Add a menu item for this extension.
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 */
function blendleimport_civicrm_navigationMenu(&$menu) {
  _blendleimport_civix_insert_navigation_menu($menu, 'Contacts', array(
    'label' => ts('Blendle Import', array('domain' => 'org.decooperatie.blendleimport')),
    'name' => 'blendleimport',
    'url' => 'civicrm/blendleimport',
    'permission' => 'edit all contacts,import contacts',
    'operator' => 'AND',
    'separator' => 0,
  ));
  _blendleimport_civix_navigationMenu($menu);
}


/* --- Default Civix hooks follow --- */


/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function blendleimport_civicrm_config(&$config) {
  _blendleimport_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function blendleimport_civicrm_xmlMenu(&$files) {
  _blendleimport_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function blendleimport_civicrm_install() {
  _blendleimport_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function blendleimport_civicrm_uninstall() {
  _blendleimport_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function blendleimport_civicrm_enable() {
  _blendleimport_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function blendleimport_civicrm_disable() {
  _blendleimport_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed
 *   Based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function blendleimport_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _blendleimport_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function blendleimport_civicrm_managed(&$entities) {
  _blendleimport_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function blendleimport_civicrm_caseTypes(&$caseTypes) {
  _blendleimport_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function blendleimport_civicrm_angularModules(&$angularModules) {
_blendleimport_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function blendleimport_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _blendleimport_civix_civicrm_alterSettingsFolders($metaDataFolders);
}
