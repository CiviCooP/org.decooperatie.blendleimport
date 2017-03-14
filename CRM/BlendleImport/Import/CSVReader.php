<?php

/**
 * Class CRM_BlendleImport_Import_CSVReader.
 * Handles parsing and storing CSV files.
 *
 * @author Kevin Levie <kevin.levie@civicoop.org>
 * @package org.decooperatie.blendleimport
 * @license AGPL-3.0
 */
class CRM_BlendleImport_Import_CSVReader {

  protected $job_id;
  protected $data;

  /**
   * CRM_BlendleImport_Import_CSVReader constructor.
   * @param int $job_id Job ID
   */
  public function __construct($job_id) {
      $this->job_id = $job_id;
  }

  /**
   * Write data from a CSV file to the import_records table.
   * @param string $data CSV Data
   * @param bool $isBase64Encoded Is $data base64 encoded?
   * @return bool Success
   * @throws CRM_BlendleImport_Exception If data could not be read / parsed
   */
  public function writeToTable($data, $isBase64Encoded = TRUE) {

    // Decode base64 if necessary
    if ($isBase64Encoded) {
      if (preg_match('@^data:[^;]*;base64,@', $data, $matches)) {
        $data = base64_decode(substr($data, strlen($matches[0])), TRUE);
        if ($data === FALSE) {
          throw new CRM_BlendleImport_Exception('Could not decode base64 encoded data: ' . htmlspecialchars(substr($data, 0, 20)));
        }
      } else {
        throw new CRM_BlendleImport_Exception('Expected URL-encoded data but got: ' . htmlspecialchars(substr($data, 0, 16)));
      }
    }

    // Parse CSV into an array of arrays
    $rows = $this->csvToArray($data);

    // Clear existing data and fetch field keys
    CRM_BlendleImport_BAO_ImportRecord::clearRecordsForJob($this->job_id);
    $validFields = CRM_BlendleImport_BAO_ImportRecord::fieldKeys();
    $bylineCache = [];

    // Walk through CSV and store all rows
    foreach($rows as $row) {

      $record = new CRM_BlendleImport_BAO_ImportRecord;
      $record->job_id = $this->job_id;

      // Check and set parent based on byline (cache)
      $record->parent = null;
      if(isset($row['byline']) && array_key_exists($row['byline'], $bylineCache)) {
        $record->parent = $bylineCache[$row['byline']];
      }

      // Set all other fields
      foreach($row as $fieldName => $value) {
        if(in_array($fieldName, $validFields)) {
          $record->$fieldName = $value;
        }
      }

      $record->save();

      if($record->parent == null) {
        $bylineCache[$row['byline']] = $record->id;
      }
      unset($record);
    }

    // Done!
    return true;
  }

  /**
   * Parse a CSV file from string into an array of arrays.
   * @param string $data CSV data
   * @return array Array of arrays
   */
  protected function csvToArray($data) {

    // Check line endings
    $data = str_replace('\r', '\n', str_replace('\r\n', '\n', $data));
    $data = str_getcsv($data, "\n");

    // Parse CSV file rows
    $csv_rows = array_map('str_getcsv', $data);
    $header_row = array_shift($csv_rows);

    // Create array with header row fields as keys
    array_walk($csv_rows, function(&$row, $key, $header) {
      $row = array_combine($header, $row);
    }, $header_row);

    // Return the new array
    return $csv_rows;
  }

}
