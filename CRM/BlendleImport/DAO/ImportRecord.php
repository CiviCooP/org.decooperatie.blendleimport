<?php

/**
 * Class CRM_BlendleImport_DAO_ImportRecord.
 * DAO for table civicrm_blendleimport_records.
 *
 * @author Kevin Levie <kevin.levie@civicoop.org>
 * @package org.decooperatie.blendleimport
 * @license AGPL-3.0
 *
 * @property int $id
 * @property int $job_id
 * @property string $product_uid
 * @property int $contact_id
 * @property string $title
 * @property string $byline
 * @property int $sales_count
 * @property int $premium_reads
 * @property int $effective_sales_count
 * @property float $refunded_amount
 * @property int $refunded_count
 * @property float $vmoney_amount
 * @property float $approximated_sales_amount_eur
 * @property float $approximated_revenue_eur
 * @property float $fb_costs
 * @property float $premium_revenue
 * @property float $matching_revenue
 * @property float $price
 * @property int $parent
 * @property string $state
 * @property string $resolution
 */
class CRM_BlendleImport_DAO_ImportRecord extends CRM_Core_DAO
{
    /**
     * Cached fields data, see functions below.
     */
    protected static $_fields = null;
    protected static $_fieldKeys = null;
    protected static $_export = null;

    /**
     * Get table name.
     * @return string
     */
    public static function getTableName()
    {
        return 'civicrm_blendleimport_records';
    }

    /**
     * Return all columns for this table.
     * @return array
     */
    public static function &fields()
    {
        if (!(self::$_fields)) {
            self::$_fields = [
                'id'                            => [
                    'name'     => 'id',
                    'type'     => CRM_Utils_Type::T_INT,
                    'title'    => ts('Import Record ID'),
                    'required' => true,
                ],
                'job_id'                        => [
                    'name'        => 'job_id',
                    'type'        => CRM_Utils_Type::T_INT,
                    'title'       => ts('Import Job ID'),
                    'required'    => true,
                    'FKClassName' => 'CRM_BlendleImport_DAO_ImportJob',
                ],
                'product_uid'                   => [
                    'name'         => 'product_uid',
                    'type'         => CRM_Utils_Type::T_STRING,
                    'maxlength'    => 100,
                    'title'        => ts('Product UID'),
                    'mapping_show' => true,
                ],
                'contact_id'                    => [
                    'name'        => 'contact_id',
                    'type'        => CRM_Utils_Type::T_INT,
                    'title'       => ts('Contact ID'),
                    'FKClassName' => 'CRM_Contact_DAO_Contact',
                    'default'     => 'NULL',
                ],
                'title'                         => [
                    'name'             => 'title',
                    'type'             => CRM_Utils_Type::T_STRING,
                    'maxlength'        => 255,
                    'title'            => ts('Title'),
                    'mapping_show'     => true,
                    'mapping_required' => true,
                ],
                'byline'                        => [
                    'name'             => 'byline',
                    'type'             => CRM_Utils_Type::T_STRING,
                    'maxlength'        => 255,
                    'title'            => ts('Byline'),
                    'mapping_show'     => true,
                    'mapping_required' => true,
                ],
                'sales_count'                   => [
                    'name'             => 'sales_count',
                    'type'             => CRM_Utils_Type::T_INT,
                    'title'            => ts('Read Count'),
                    'mapping_show'     => true,
                    'mapping_required' => true,
                ],
                'approximated_revenue_eur'      => [
                    'name'             => 'approximated_revenue_eur',
                    'type'             => CRM_Utils_Type::T_FLOAT,
                    'title'            => ts('Revenue'),
                    'mapping_show'     => true,
                    'mapping_required' => true,
                ],
                'vmoney_amount'                 => [
                    'name'             => 'vmoney_amount',
                    'type'             => CRM_Utils_Type::T_FLOAT,
                    'title'            => ts('Donations'),
                    'mapping_show'     => true,
                    'mapping_required' => true,
                ],
                'premium_reads'                 => [
                    'name'         => 'premium_reads',
                    'type'         => CRM_Utils_Type::T_INT,
                    'title'        => ts('Premium Reads'),
                    'mapping_show' => true,
                ],
                'effective_sales_count'         => [
                    'name'  => 'effective_sales_count',
                    'type'  => CRM_Utils_Type::T_INT,
                    'title' => ts('Effective Sales Count'),
                ],
                'refunded_amount'               => [
                    'name'         => 'refunded_amount',
                    'type'         => CRM_Utils_Type::T_FLOAT,
                    'title'        => ts('Refunded Amount'),
                    'mapping_show' => true,
                ],
                'refunded_count'                => [
                    'name'         => 'refunded_count',
                    'type'         => CRM_Utils_Type::T_INT,
                    'title'        => ts('Refunded Count'),
                    'mapping_show' => true,
                ],
                'sales_amount'                  => [
                    'name'         => 'sales_amount',
                    'type'         => CRM_Utils_Type::T_FLOAT,
                    'title'        => ts('Sales Amount'),
                    'mapping_show' => true,
                ],
                'approximated_sales_amount_eur' => [
                    'name'  => 'approximated_sales_amount_eur',
                    'type'  => CRM_Utils_Type::T_FLOAT,
                    'title' => ts('Sales Amount'),
                ],
                'fb_costs'                      => [
                    'name'         => 'fb_costs',
                    'type'         => CRM_Utils_Type::T_FLOAT,
                    'title'        => ts('FB Costs'),
                    'mapping_show' => true,
                ],
                'premium_revenue'               => [
                    'name'         => 'premium_revenue',
                    'type'         => CRM_Utils_Type::T_FLOAT,
                    'title'        => ts('Premium Revenue'),
                    'mapping_show' => true,
                ],
                'matching_revenue'              => [
                    'name'         => 'matching_revenue',
                    'type'         => CRM_Utils_Type::T_FLOAT,
                    'title'        => ts('Matching'),
                    'mapping_show' => true,
                ],
                'price'                         => [
                    'name'  => 'price',
                    'type'  => CRM_Utils_Type::T_FLOAT,
                    'title' => ts('Price'),
                ],
                'parent'                        => [
                    'name'        => 'parent',
                    'type'        => CRM_Utils_Type::T_INT,
                    'title'       => ts('Parent Record ID'),
                    'default'     => null,
                    'FKClassName' => 'CRM_BlendleImport_DAO_ImportRecord',
                ],
                'state'                         => [
                    'name'      => 'state',
                    'type'      => CRM_Utils_Type::T_STRING,
                    'maxlength' => 12,
                    'title'     => ts('Contact Matching State'),
                    'default'   => 'NULL',
                ],
                'resolution'                    => [
                    'name'      => 'resolution',
                    'type'      => CRM_Utils_Type::T_STRING,
                    'maxlength' => 4096,
                    'title'     => ts('Contact Matching Resolution'),
                    'default'   => 'NULL',
                ],
            ];
        }
        return self::$_fields;
    }

    /**
     * Returns an array containing, for each field, the array key used for that.
     * field in self::$_fields.
     * @return array
     */
    public static function &fieldKeys()
    {
        if (!(self::$_fieldKeys)) {
            $fields = self::fields();
            self::$_fieldKeys = [];
            foreach ($fields as $name => $field) {
                self::$_fieldKeys[$name] = $field['name'];
            }
        }
        return self::$_fieldKeys;
    }

    /**
     * Returns the list of fields that can be exported.
     * @param mixed $prefix
     * @return array
     */
    public static function &export($prefix = false)
    {
        if (!(self::$_export)) {
            self::$_export = [];
            $fields = self::fields();
            foreach ($fields as $name => $field) {
                if (CRM_Utils_Array::value('export', $field)) {
                    if ($prefix) {
                        self::$_export['activity'] = &$fields[$name];
                    } else {
                        self::$_export[$name] = &$fields[$name];
                    }
                }
            }
        }
        return self::$_export;
    }

    /**
     * Returns an array containing all field keys of numeric fields in self::$_fields.
     * @return array
     */
    public static function &numericFieldKeys()
    {
        $fields = self::fields();
        $numericFields = [];
        foreach ($fields as $name => $field) {
            if (in_array($field['type'], [CRM_Utils_Type::T_INT, CRM_Utils_Type::T_FLOAT])) {
                $numericFields[] = $field['name'];
            }
        }
        return $numericFields;
    }
}
