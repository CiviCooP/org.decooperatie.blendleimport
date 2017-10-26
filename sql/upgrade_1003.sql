ALTER TABLE `civicrm_blendleimport_records`
  ADD `premium_revenue` FLOAT(10,2) UNSIGNED NOT NULL AFTER `fb_costs`,
  ADD `matching_revenue` FLOAT(10,2) UNSIGNED NOT NULL AFTER `premium_revenue`;
