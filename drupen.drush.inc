<?php

require_once __DIR__ . '/vendor/autoload.php';

/**
 * @file
 * A few helpful Drush commands for security auditing.
 */

use Drupal\drupen\DrupenServiceProvider;

/**
 * Implementation of hook_drush_help().
 */
function drupen_drush_help($section) {
  switch ($section) {
    case 'meta:drupen:title':
      return dt('Drupen');
    case 'meta:drupen:summary':
      return dt('Drupal Penetration test helper.');
  }
}

/**
 * Implementation of hook_drush_command().
 */
function drupen_drush_command() {
  $items['route-list'] = array(
    'description' => 'List all route entries as valid urls.',
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_FULL,
    'options' => array(
      'route-name' => 'Filter by a single route.',
    ),
  );
  $items['route-test'] = array(
    'description' => 'Test access to all route entries.',
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_FULL,
    'options' => array(
      'route-name' => 'Filter by a single route.',
      'response-code' => 'Filter routes that respond with the provided HTTP code.',
      'response-cache' => 'Filter routes that have a X-Drupal-Cache value.',
      'profile' => 'Display response timing information.',
      'cookie' => 'Provide cookies to send with requests (for authentication).',
      'verify-ssl' => 'Verify the SSL certificate for responses.',
      'follow-redirects' => 'Follow HTTP redirects.',
    ),
  );
  $items['session-cookie'] = array(
    'description' => 'Output a session cookie.',
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_FULL,
    'arguments' => array(
      'username' => 'Username for login.',
      'password' => 'Password for login.',
    ),
  );
  return $items;
}

/**
 * Implements hook_drush_init().
 */
function drupen_drush_init() {
  add_services_to_container();
  \Drupal::service('drupen.io')->setupIo(new DrupenDrush8Io(), 'dt');
}

function drush_drupen_pre_route_list($profile = NULL) {
  // @TODO Not sure if this function is needed.
  $sql = drush_sql_get_class();
  if (!$db_spec = $sql->db_spec()) {
    drush_set_error(dt('Could not determine database connection parameters. Pass --db-url option.'));
    return;
  }

  // Make sure URI is set so we get back a proper $alias_record. Needed for quick-drupal.
  _drush_bootstrap_selected_uri();
	
  $alias_record = drush_sitealias_get_record('@self');
  $sites_subdir = drush_sitealias_local_site_path($alias_record);
  // Override with sites-subdir if specified.
  if ($dir = drush_get_option('sites-subdir')) {
    $sites_subdir = "sites/$dir";
  }
  $conf_path = $sites_subdir;
}

/**
 * This is necessary to define our own services.
 */
function add_services_to_container() {
  \Drupal::service('kernel')->addServiceModifier(new DrupenServiceProvider());
  \Drupal::service('kernel')->rebuildContainer();
}

/**
 * Command argument complete callback.
 */
function drush_drupen_route_list() {
  \Drupal::service('drupen.drupen')->routeList(get_command_options());
}


function drush_drupen_pre_route_test($profile = NULL) {
  // @TODO Not sure if this function is needed.
  $sql = drush_sql_get_class();
  if (!$db_spec = $sql->db_spec()) {
    drush_set_error(dt('Could not determine database connection parameters. Pass --db-url option.'));
    return;
  }

  // Make sure URI is set so we get back a proper $alias_record. Needed for quick-drupal.
  _drush_bootstrap_selected_uri();

  $alias_record = drush_sitealias_get_record('@self');
  $sites_subdir = drush_sitealias_local_site_path($alias_record);
  // Override with sites-subdir if specified.
  if ($dir = drush_get_option('sites-subdir')) {
    $sites_subdir = "sites/$dir";
  }
  $conf_path = $sites_subdir;
}

/**
 * Command argument complete callback.
 */
function drush_drupen_route_test() {
  \Drupal::service('drupen.drupen')->routeTest(get_command_options());
}

/**
 * Command argument complete callback.
 */
function drush_drupen_session_cookie($username = '', $password = '') {
  \Drupal::service('drupen.drupen')->sessionCookie($username, $password);
}

/**
 * Helper function to get command options and values.
 */
function get_command_options() {
  $command = drush_parse_command();
  $command_options = array_keys(_drush_get_command_options($command));
  $options = [];
  foreach ($command_options as $option) {
    $options[$option] = drush_get_option($option, FALSE);
  }
  return $options;
}

// @codingStandardsIgnoreStart
/**
 * Class DrupenDrush8Io.
 *
 * This is a stand in for \Symfony\Component\Console\Style\StyleInterface with
 * drush 8 so that we don't need to depend on symfony components.
 */
class DrupenDrush8Io {

  public function confirm($text) {
    return drush_confirm($text);
  }

  public function success($text) {
    drush_print($text);
  }

  public function error($text) {
    drush_print($text);
  }

  public function text($text) {
    drush_print($text);
  }
}
// @codingStandardsIgnoreEnd
