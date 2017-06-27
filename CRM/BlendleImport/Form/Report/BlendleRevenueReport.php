<?php

/**
 * Class CRM_BlendleImport_Form_Report_BlendleRevenueReport.
 * Provides an overview of generated contributions and activities.
 * Currently mostly hard-coded: only 'Import Job ID' is available as a filter.
 *
 * @author Kevin Levie <kevin.levie@civicoop.org>
 * @package org.decooperatie.blendleimport
 * @license AGPL-3.0
 */
class CRM_BlendleImport_Form_Report_BlendleRevenueReport extends CRM_Report_Form {

  protected $_summary = NULL;
  protected $_noFields = TRUE;

  function __construct() {
    $this->_columns = [
      'civicrm_activity' => [
        'filters' => [
          'import_job_id' => [
            'title'   => ts('Import Job ID'),
            'type'    => CRM_Report_Form::OP_INT,
            'dbAlias' => 'abl.import_job_id_55',
          ],
        ],
      ],
    ];
    parent::__construct();
  }

  public function select() {
    $this->_select = "SELECT 
      c.id AS contact_id, 
      c.sort_name AS contact_sort_name,
      e.email AS contact_email,
      coad.ro_wordpress_id_44 AS ro_wordpress_id,
      abl.import_job_id_55 AS import_job_id, 
      coj.id AS contribution_id,
      coj.contribution_subject AS contribution_subject,
      coj.total_amount AS contribution_total_amount,
      coj.fee_amount AS contribution_fee_amount,
      coj.net_amount AS contribution_net_amount,
      coj.receive_date AS contribution_receive_date, 
      a.id AS activity_id,
      a.activity_date_time AS activity_date, 
      abl.product_uid_56 AS activity_product_uid,
      abl.author_77 AS activity_author,
      abl.article_title_57 AS activity_article_title,
      abl.sales_count_58 AS activity_sales_count,
      abl.premium_reads_59 AS activity_premium_reads,
      abl.refunded_count_60 AS activity_refunded_count,
      abl.refunded_amount_61 AS activity_refunded_amount,
      abl.vmoney_amount_62 AS activity_vmoney_amount,
      abl.sales_amount_63 AS activity_sales_amount,
      abl.revenue_64 AS activity_revenue,
      abl.price_65 AS activity_article_price,
      abl.fbcosts_68 AS activity_fb_costs
      ";
  }

  public function from() {
    $this->_from = "FROM civicrm_value_blendle_import_11 abl
      LEFT JOIN civicrm_activity a ON abl.entity_id = a.id
      LEFT JOIN civicrm_activity_contact ac ON a.id = ac.activity_id AND ac.record_type_id = 3
      LEFT JOIN civicrm_contact c ON ac.contact_id = c.id
      LEFT JOIN civicrm_email e ON c.id = e.contact_id AND e.is_primary = 1
      LEFT JOIN civicrm_value_administrative_data_10 coad ON c.id = coad.entity_id
      LEFT JOIN (
        SELECT co.id, co.contact_id, co.total_amount, co.fee_amount, co.net_amount, co.receive_date, cobl.import_job_id_68, coac.subject AS contribution_subject
        FROM civicrm_contribution co
        LEFT JOIN civicrm_value_blendle_import_contributions__12 cobl ON co.id = cobl.entity_id
        LEFT JOIN civicrm_activity coac ON co.id = coac.source_record_id AND coac.activity_type_id = 6
      ) coj ON coj.contact_id = c.id AND coj.import_job_id_68 = abl.import_job_id_55
    ";
  }

  public function where() {
    parent::where();

    if (empty($this->_whereClauses)) {
      $this->_where = " WHERE abl.import_job_id_55 IS NOT NULL";
    }
  }

  function orderBy() {
    $this->_orderBy = " ORDER BY sort_name ASC, import_job_id ASC, activity_id ASC";
  }

  public function preProcess() {
    $this->assign('reportTitle', ts('Blendle Revenue Report'));
    return parent::preProcess();
  }

  public function postProcess() {

    $this->_columnHeaders = [
        'contact_id'                => ['title' => ts('Contact ID'), 'type' => CRM_Report_Form::OP_INT],
        'contact_sort_name'         => ['title' => ts('Contact Name'), 'type' => CRM_Report_Form::OP_STRING],
        'contact_email'             => ['title' => ts('Email'), 'type' => CRM_Report_Form::OP_STRING],
        'ro_wordpress_id'           => ['title' => ts('RO WPID'), 'type' => CRM_Report_Form::OP_STRING],
        'contribution_id'           => ['title' => ts('Contribution ID'), 'type' => CRM_Report_Form::OP_INT],
        'contribution_subject'      => ['title' => ts('Contribution'), 'type' => CRM_Report_Form::OP_STRING],
        'contribution_total_amount' => ['title' => ts('Total Amount'), 'type' => CRM_Report_Form::OP_FLOAT],
        'contribution_receive_date' => ['title' => ts('Date'), 'type' => CRM_Report_Form::OP_DATE],
        'activity_id'               => ['title' => ts('Activity ID'), 'type' => CRM_Report_Form::OP_INT],
        'activity_author'           => ['title' => ts('Author'), 'type' => CRM_Report_Form::OP_STRING],
        'activity_article_title'    => ['title' => ts('Article Title'), 'type' => CRM_Report_Form::OP_STRING],
        'activity_sales_count'      => ['title' => ts('Sales Count'), 'type' => CRM_Report_Form::OP_INT],
        'activity_premium_reads'    => ['title' => ts('Premium Reads'), 'type' => CRM_Report_Form::OP_INT],
        'activity_refunded_amount'  => ['title' => ts('Refunded Amount'), 'type' => CRM_Report_Form::OP_FLOAT],
        'activity_vmoney_amount'    => ['title' => ts('Vmoney Amount'), 'type' => CRM_Report_Form::OP_FLOAT],
        'activity_revenue'          => ['title' => ts('Revenue'), 'type' => CRM_Report_Form::OP_FLOAT],
        'activity_fb_costs'         => ['title' => ts('Campaign Costs'), 'type' => CRM_Report_Form::OP_FLOAT],
    ];

    parent::postProcess();
  }

  public function alterDisplay(&$rows) {
    $prevContactId = NULL;
    $prevContributionId = NULL;

    foreach ($rows as $rowNum => &$row) {

      $hideContactColumn = ($prevContactId == $row['contact_id']);
      $prevContactId = $row['contact_id'];

      $hideContributionColumn = ($prevContributionId == $row['contribution_id']);
      $prevContributionId = $row['contribution_id'];

      foreach ($row as $colName => $colVal) {
        if ($hideContactColumn && in_array($colName, ['contact_id', 'contact_sort_name','contact_email','ro_wordpress_id'])) {
          unset($rows[$rowNum][$colName]);
        }

        if ($hideContributionColumn && in_array($colName, ['contribution_id', 'contribution_subject', 'contribution_total_amount', 'contribution_receive_date'])) {
          unset($rows[$rowNum][$colName]);
        }
      }

      if (!empty($row['contact_sort_name'])) {
        $row['contact_sort_name_link'] = CRM_Utils_System::url('civicrm/contact/view', 'reset=1&cid=' . $row['contact_id'], $this->_absoluteUrl);
        $row['contact_sort_name_hover'] = ts('View Contact');
      }
      if (!empty($row['contact_email'])) {
        $row['contact_email_link'] = 'mailto:' . $row['contact_email'];
        $row['contact_email_hover'] = ts('Email Contact');
      }
      if (!empty($row['contribution_subject'])) {
        $row['contribution_subject_link'] = CRM_Utils_System::url('civicrm/contact/view/contribution', 'action=view&reset=1&cid=' . $row['contact_id'] . '&id=' . $row['contribution_id'], $this->_absoluteUrl);
        $row['contribution_subject_hover'] = ts('View Contribution');
      }
      if (!empty($row['activity_article_title'])) {
        $row['activity_article_title_link'] = CRM_Utils_System::url('civicrm/activity', 'action=view&reset=1&cid=' . $row['contact_id'] . '&id=' . $row['activity_id'], $this->_absoluteUrl);
        $row['activity_article_title_hover'] = ts('View Activity');
      }

      if ($row['contribution_total_amount'] < 0) {
        $row['contribution_total_amount'] = number_format(abs($row['contribution_total_amount']), 2, '.', '');
      }
    }

    if($this->_outputMode != 'csv') {
      unset($this->_columnHeaders['contact_id']);
    }

    unset($this->_columnHeaders['contribution_id']);
    unset($this->_columnHeaders['activity_id']);
  }


}
