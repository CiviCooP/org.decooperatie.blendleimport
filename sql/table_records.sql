CREATE TABLE IF NOT EXISTS `civicrm_blendleimport_records` (
  id                            INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  job_id                        INT(10) UNSIGNED NOT NULL,
  product_uid                   VARCHAR(100)              DEFAULT NULL,
  contact_id                    INT(10) UNSIGNED          DEFAULT NULL,
  title                         VARCHAR(255)              DEFAULT NULL,
  byline                        VARCHAR(255)              DEFAULT NULL,
  sales_count                   INT(10) UNSIGNED          DEFAULT NULL,
  premium_reads                 INT(10) UNSIGNED          DEFAULT NULL,
  effective_sales_count         INT(10) UNSIGNED          DEFAULT NULL,
  refunded_amount               FLOAT(10, 2) UNSIGNED     DEFAULT NULL,
  refunded_count                INT(10) UNSIGNED          DEFAULT NULL,
  vmoney_amount                 FLOAT(10, 2) UNSIGNED     DEFAULT NULL,
  sales_amount                  FLOAT(10, 2) UNSIGNED     DEFAULT NULL,
  approximated_sales_amount_eur FLOAT(10, 2) UNSIGNED     DEFAULT NULL,
  approximated_revenue_eur      FLOAT(10, 2) UNSIGNED     DEFAULT NULL,
  price                         FLOAT(6, 4) UNSIGNED      DEFAULT NULL,
  is_unique                     INT(1) UNSIGNED           DEFAULT NULL,
  state                         VARCHAR(12)               DEFAULT NULL,
  resolution                    VARCHAR(4096)             DEFAULT NULL,
  PRIMARY KEY (id)
)
  ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8
  COLLATE = utf8_general_ci;

ALTER TABLE `civicrm_blendleimport_records`
  ADD KEY `job_id` (`job_id`),
  ADD KEY `contact_id` (`contact_id`),
  ADD KEY `state` (`state`),
  ADD KEY `is_unique` (`is_unique`),
  ADD CONSTRAINT `FK_civicrm_blendleimport_records_job_id` FOREIGN KEY (`job_id`) REFERENCES `civicrm_blendleimport_job` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_civicrm_blendleimport_records_contact_id` FOREIGN KEY(`contact_id`) REFERENCES `civicrm_contact` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;
