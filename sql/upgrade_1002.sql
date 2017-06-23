ALTER TABLE `civicrm_blendleimport_job`
  ADD `data` LONGTEXT NULL AFTER `created_id`,
  ADD `mapping` MEDIUMTEXT NULL AFTER `data`;