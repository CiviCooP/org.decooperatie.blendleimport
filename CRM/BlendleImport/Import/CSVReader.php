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
   * Read first row of CSV and store a provisional field mapping.
   * @param string $data CSV Data
   * @return array Mapping data
   */
  public function parseColumns($data) {

    $data = $this->decodeBase64($data);
    $headerRow = $this->csvToArray($data, TRUE);
    $validFields = CRM_BlendleImport_BAO_ImportRecord::fields();
    $mapping = [];

    foreach($validFields as $recordField) {
      if(!isset($recordField['mapping_show']) || $recordField['mapping_show'] !== TRUE) {
        continue;
      }

      if(in_array($recordField['name'], $headerRow)) {
        $mapping[$recordField['name']] = $recordField['name'];
      } else {
        $mapping[$recordField['name']] = '';
      }
    }

    $mappingData = ['mapping' => $mapping, 'columns' => $headerRow];
    return $mappingData;
  }

  /**
   * Write data from a CSV file to the import_records table.
   * @param string $data CSV Data
   * @param array $mapping CSV Column Mapping
   * @return bool Success
   * @throws CRM_BlendleImport_Exception If data could not be read / parsed
   */
  public function writeToTable($data, $mapping = []) {

    // Parse CSV into an array of arrays
    $rows = $this->csvToArray($data);
    if(!$rows || count($rows) == 0) {
      return FALSE;
    }

    // Clear existing data and fetch field keys
    CRM_BlendleImport_BAO_ImportRecord::clearRecordsForJob($this->job_id);
    $validFields = CRM_BlendleImport_BAO_ImportRecord::fieldKeys();
    $numericFields = CRM_BlendleImport_BAO_ImportRecord::numericFieldKeys();
    $bylineCache = [];

    $mappedFields = [];
    if(!empty($mapping) && count($mapping['mapping']) > 0) {
      $mappedFields = $mapping['mapping'];
    }

    // Walk through CSV and store all rows
    foreach ($rows as $row) {

      $record = new CRM_BlendleImport_BAO_ImportRecord;
      $record->job_id = $this->job_id;

      // Check and set parent based on byline (cache)
      $record->parent = NULL;
      if (isset($row['byline']) && array_key_exists($row['byline'], $bylineCache)) {
        $record->parent = $bylineCache[$row['byline']];
      }

      // Set all other fields
      foreach ($row as $fieldName => $value) {
        $mappedFieldName = array_search($fieldName, $mappedFields);
        if(empty($mappedFieldName)) {
          $mappedFieldName = $fieldName;
        }

        if (in_array($mappedFieldName, $validFields)) {
          if(in_array($mappedFieldName, $numericFields)) {
            $value = (float) str_replace(',', '.', $value);
          }

          $record->$mappedFieldName = $value;
        }
      }

      $record->save();

      if ($record->parent == NULL) {
        $bylineCache[$row['byline']] = $record->id;
      }
      unset($record);
    }

    // Done!
    return TRUE;
  }

  /**
   * Decode base64 encoded data
   * @param string $data Data
   * @return string Data
   * @throws CRM_BlendleImport_Exception If data invalid
   */
  protected function decodeBase64($data) {
    // Decode base64 if necessary
    if (preg_match('@^data:[^;]*;base64,@', $data, $matches)) {
      $data = base64_decode(substr($data, strlen($matches[0])), TRUE);
      if ($data === FALSE) {
        throw new CRM_BlendleImport_Exception('Could not decode base64 encoded data: ' . htmlspecialchars(substr($data, 0, 20)));
      }
    }
    return $data;
  }

  /**
   * Parse a CSV file from string into an array of arrays.
   * @param string $data CSV data
   * @param bool $onlyHeader Return header row only?
   * @return array Array of arrays
   */
  protected function csvToArray($data, $onlyHeader = FALSE) {

    // Check line endings
    $data = str_replace("\r", "\n", str_replace("\r\n", "\n", $data));
    $data = str_getcsv($data, "\n");

    if(count($data) == 0) {
      return [];
    }

    // Try to detect delimiter
    $delimiter = $this->detectCsvDelimiter($data);

    // Return header only?
    if($onlyHeader) {
      return str_getcsv($data[0], $delimiter);
    }

    // Parse CSV file rows
    $csv_rows = array_map(function ($line) use ($delimiter) {
      return str_getcsv($line, $delimiter);
    }, $data);
    $header_row = array_shift($csv_rows);

    // Create array with header row fields as keys
    array_walk($csv_rows, function (&$row, $key, $header) {
      $row = array_combine($header, $row);
    }, $header_row);

    // Return the new array
    return $csv_rows;
  }

  /**
   * Try to detect the delimiter for a CSV file.
   * Borrowed from
   * http://stackoverflow.com/questions/3395267/how-to-find-out-if-csv-file-fields-are-tab-delimited-or-comma-delimited
   * @param array $data CSV data as array
   * @param int $checkLines Number of lines to check
   * @return string
   */
  protected function detectCsvDelimiter($data, $checkLines = 5) {
    $delimiters = [',', '\t', ';', '|', ':'];
    $results = [];
    $i = 0;
    while (count($data) > 0 && $i <= $checkLines) {
      $line = array_shift($data);
      foreach ($delimiters as $delimiter) {
        $regExp = '/[' . $delimiter . ']/';
        $fields = preg_split($regExp, $line);
        if (count($fields) > 1) {
          if (!empty($results[$delimiter])) {
            $results[$delimiter] ++;
          } else {
            $results[$delimiter] = 1;
          }
        }
      }
      $i ++;
    }
    $results = array_keys($results, max($results));
    return $results[0];
  }

}
