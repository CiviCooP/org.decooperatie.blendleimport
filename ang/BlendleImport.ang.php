<?php
// This file declares an Angular module which can be autoloaded
// in CiviCRM. See also:
// http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules

return [
  'js'       =>
    [
      0 => 'ang/BlendleImport.js',
      1 => 'ang/BlendleImport/*.js',
      2 => 'ang/BlendleImport/*/*.js',
    ],
  'css'      =>
    [
      0 => 'ang/BlendleImport.css',
    ],
  'partials' =>
    [
      0 => 'ang/BlendleImport',
    ],
  'settings' =>
    [
    ],
];