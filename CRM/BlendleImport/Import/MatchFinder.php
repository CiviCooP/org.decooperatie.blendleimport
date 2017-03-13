<?php

/**
 * Class CRM_BlendleImport_Import_MatchFinder.
 * Find matches for import records.
 * These functions are inspired by CsvImportHelper->findContact.
 *
 * @author Kevin Levie <kevin.levie@civicoop.org>
 * @package org.decooperatie.blendleimport
 * @license AGPL-3.0
 */
class CRM_BlendleImport_Import_MatchFinder {

  /**
   * @var static Class instance
   */
  protected static $instance;

  /**
   * @return static Class instance
   */
  static public function instance() {
    if (! static::$instance) {
      static::$instance = new static;
    }
    return self::$instance;
  }

  /**
   * Find matches based on a record's byline field, and store results
   * in the import record's 'status' and 'resolution' fields.
   * @param CRM_BlendleImport_BAO_ImportRecord $record
   * @return bool Match found?
   */
  public function match(CRM_BlendleImport_BAO_ImportRecord &$record) {

    $names = self::cleanupName($record->byline, $record->title);

    // Store updated name with button / publication rules applied to show in listing
    $record->byline = implode(' ', $names);

    // No name at all?
    if (empty($names)) {
      $this->storeResult([], $record, 'Name field empty');
      return FALSE;
    }

    // Clean up voorvoegsels / middle names for matching
    $names['last'] = str_replace(['van ', 'de ', '\'t ', 't ', 'v ', 'en '], '', $names['last']);

    // 1. Try to find contact by full literal first and last name
    $result = $this->findContacts($names['last'], $names['first']);
    if ($result) {
      $this->storeResult($result, $record, 'First and last name');
      return TRUE;
    }

    // 2. Try to find contact(s) by literal last name only
    $result = $this->findContacts($names['last'], NULL);
    if ($result) {
      $this->storeResult($result, $record, 'Last name');
      return TRUE;
    }

    // 3. Try to find contact(s) by %last name% only
    $result = $this->findContacts($names['last'], NULL, TRUE);
    if ($result) {
      $this->storeResult($result, $record, 'Last name with wildcard');
      return TRUE;
    }

    // 4. Try to find contact(s) by %last word of last name% only
    if (preg_match('/([^ ]+) (.+)/', $names['last'], $matches)) {
      $result = $this->findContacts($matches[2], NULL, TRUE);
      if ($result) {
        $this->storeResult($result, $record, 'Last word of last name');
        return TRUE;
      }
    }

    // Nothing found
    $this->storeResult([], $record, 'No matches found');
    return FALSE;
  }

  /**
   * Try to find a name in byline and/or title and return an array with [first, last] name.
   * This function is both called above and when creating contacts.
   * @param string|null $names Article Byline
   * @param string|null $title Article Title
   * @return array|bool Array with first/last name, or false if name was empty.
   */
  public static function cleanupName($names, $title = NULL) {

    $names = trim($names);

    // If no byline but [Button] in title...
    if (empty($names) && !empty($title) && preg_match('/^\[Button\]([- ]+)?([^-]+)[- ].*$/', trim($title), $matches)) {
      $names = trim($matches[2]);
    }

    // No name at all?
    if(empty($names)) {
      return FALSE;
    }

    // Remove /Publication part from names
    if (preg_match('/^([^\/]+)\/(.*)$/', $names, $matches)) {
      $names = $matches[1];
    }

    // Check for name format: Last, First - otherwise assume First Last [Last Last]
    if (preg_match('/^([^,]+)\s*,\s*([^,]+)$/', $names, $matches)) {
      $lastName = $matches[1];
      $firstName = trim($matches[2]);
    } else {
      $splitNames = preg_split('/\s+/', $names);
      if (count($splitNames) == 1) {
        $lastName = array_shift($splitNames);
        $firstName = NULL;
      } else {
        $firstName = array_shift($splitNames);
        $lastName = implode(' ', $splitNames);
      }
    }

    // Return found [first, last] names
    return ['first' => $firstName, 'last' => $lastName];
  }

  /**
   * Find contacts by first and/or last name
   * @param string $lastName Last Name
   * @param string|null $firstName First Name
   * @param bool $useLike Use LIKE instead of =
   * @return array|bool Results, or false if nothing found
   */
  protected function findContacts($lastName, $firstName = NULL, $useLike = FALSE) {
    if ($useLike) {
      if (!empty($lastName)) {
        $lastName = ['LIKE' => '%' . $lastName];
      }
      if (!empty($firstName)) {
        $firstName = ['LIKE' => $firstName . '%'];
      }
    }

    $result = civicrm_api3('Contact', 'get', [
      'sequential' => 1,
      'is_deleted' => 0,
      'last_name'  => $lastName,
      'first_name' => $firstName,
      'return'     => 'display_name',
      'options'    => ['limit' => 10],
    ]);

    if (!$result || $result['count'] == 0) {
      return FALSE;
    }

    return $result['values'];
  }

  /**
   * Store results to an import record object
   * @param mixed $result Result from findContacts
   * @param CRM_BlendleImport_BAO_ImportRecord $record Import Record
   * @param string $matchTypeDescr Match type description
   * @return void
   */
  protected function storeResult($result, &$record, $matchTypeDescr) {
    if (count($result) > 1) {
      $record->state = 'multiple';
      $record->contact_id = NULL;
      $record->resolution = [];
      foreach ($result as $row) {
        $record->resolution[$row['contact_id']] = [
          'contact_id' => $row['contact_id'],
          'match'      => $matchTypeDescr,
          'name'       => $row['display_name'],
        ];
      }
    } elseif (count($result) == 1) {
      $record->state = 'found';
      $record->contact_id = $result[0]['contact_id'];
      $record->resolution = [
        $result[0]['contact_id'] => [
          'contact_id' => $result[0]['contact_id'],
          'match'      => $matchTypeDescr,
          'name'       => $result[0]['display_name'],
        ],
      ];
    } else {
      $record->state = 'impossible';
      $record->contact_id = NULL;
      $record->resolution = [];
    }

    $record->resolution = serialize($record->resolution);
    $record->save();
  }


}
