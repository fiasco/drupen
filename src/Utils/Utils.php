<?php

namespace Drupal\drupen\Utils;

class Utils {

  const DRUPEN_STRING_SEPERATOR = '~~~';

  /**
   * Helper function to generate permutations of N sized arrays.
   *
   * @param string $separator
   * @param array $results
   * @param \array[] ...$arrays
   */
  public static function generatePermutations($separator, array &$results, array ...$arrays) {
    $empty = empty($results);
    $array = array_shift($arrays);
    $new_results = [];
    foreach ($array as $key => $value) {
      if ($empty) {
        $results[] = $value;
      }
      else {
        foreach ($results as $result) {
          $new_results[$key][] = $result . $separator . $value;
        }
      }
    }
    if ($new_results) {
      $results = array_merge(...$new_results);
    }
    if (count($arrays)) {
      self::generatePermutations($separator, $results, ...$arrays);
    }
  }

  // Helper function to render an absolute link.
  public static function renderLink($name, $params = []) {
    try {
      $url = \Drupal::urlGenerator()
        ->generateFromRoute($name, $params, ['absolute' => TRUE]);
      return $url;
    }
    catch (\Exception $e) {
      if (drush_get_context('DRUSH_VERBOSE')) {
        //$this->logger()->error('Skipping @route: Error generating url.', ['@route' => $name]);
      }
      return null;
    }
  }

}
