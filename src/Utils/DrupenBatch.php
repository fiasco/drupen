<?php

namespace Drupal\drupen\Utils;

class DrupenBatch {

  public static function _drush_bg_callback_process_route_batch($chunk, $details, $options, &$context) {
    $context['message'] = $details;

    foreach ($chunk as $url) {
      $urlLoader = new UrlLoader($options['cookie'], $options['response-code'], $options['response-cache'], $options['profile'], $options['verify-ssl'], $options['follow-redirects']);
      $urlLoader->loadURL($url);
    }
  }

  /**
   * This callback is called when the batch process finishes.
   */
  public static function _drush_bg_callback_process_route_batch_finished($success, $results, $operations) {
    if ($success) {
      // Let the user know we have finished.
      drush_log(dt('All routes were tested.'), 'ok');
    }
  }

}
