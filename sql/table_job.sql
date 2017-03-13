CREATE TABLE IF NOT EXISTS `civicrm_blendleimport_job` (
  id                  INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  name                VARCHAR(255)     NULL,
  publication         VARCHAR(100)     NULL,
  import_date         DATETIME         NULL,
  add_tag             INT(10) UNSIGNED NULL,
  add_membership_type INT(11) UNSIGNED NULL,
  status              VARCHAR(20)      NULL,
  created_date        DATETIME         NULL,
  created_id          INT(10) UNSIGNED NULL,
  PRIMARY KEY (id)
)
  ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8
  COLLATE = utf8_general_ci;

ALTER TABLE `civicrm_blendleimport_job`
  ADD KEY `created_id` (`created_id`),
  ADD KEY `add_tag` (`add_tag`),
  ADD KEY `add_membership_type` (`add_membership_type`),
  ADD CONSTRAINT `FK_civicrm_blendleimport_job_created_id` FOREIGN KEY (`created_id`) REFERENCES `civicrm_contact` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `FK_civicrm_blendleimport_job_add_tag` FOREIGN KEY (`add_tag`) REFERENCES `civicrm_tag` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `FK_civicrm_blendleimport_job_add_membership_type` FOREIGN KEY (`add_membership_type`) REFERENCES `civicrm_membership_type` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;
