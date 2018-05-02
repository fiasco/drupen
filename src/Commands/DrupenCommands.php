<?php

namespace Drush\Commands;

require_once __DIR__ . '/../../vendor/autoload.php';

use Drush\Commands\DrushCommands;
use Drupal\drupen\Drupen;
use Drupal\drupen\DrupenServiceProvider;

/**
 * A Drush commandfile.
 *
 * In addition to this file, you need a drush.services.yml
 * in root of your module, and a composer.json file that provides the name
 * of the services file to use.
 *
 * See these files for an example of injecting Drupal services:
 *   - http://cgit.drupalcode.org/devel/tree/src/Commands/DevelCommands.php
 *   - http://cgit.drupalcode.org/devel/tree/drush.services.yml
 */
class DrupenCommands extends DrushCommands {

  /**
   * @hook pre-command *
   */
  public function setupDrupenServices()
  {
    $this->addServicesToContainer();
  }

  /**
   * This is necessary to define our own services.
   */
  protected function addServicesToContainer() {
    \Drupal::service('kernel')->addServiceModifier(new DrupenServiceProvider());
    \Drupal::service('kernel')->rebuildContainer();
  }

  /**
   * List all route entries as valid urls.
   *
   * @param array $options An associative array of options whose values come from cli, aliases, config, etc.
   * @option route-name
   *   Filter by a single route.
   *
   * @command route:list
   * @aliases route-list
   * @bootstrap DRUSH_BOOTSTRAP_DRUPAL_FULL
   */
  public function routeList(array $options = ['route-name' => null]) {
    /** @var \Drupal\drupen\Drupen $drupen **/
    $drupen = \Drupal::service('drupen.drupen');
    $drupen->routeList($options, $this->io(), 'dt');
  }

  /**
   * Test access to all route entries.
   *
   * @param array $options An associative array of options whose values come from cli, aliases, config, etc.
   * @option route-name
   *   Filter by a single route.
   * @option response-code
   *   Filter routes that respond with the provided HTTP code.
   * @option response-cache
   *   Filter routes that have a X-Drupal-Cache value.
   * @option profile
   *   Display response timing information.
   * @option cookie
   *   Provide cookies to send with requests (for authentication).
   * @option verify-ssl
   *   Verify the SSL certificate for responses.
   * @option follow-redirects
   *   Follow HTTP redirects.
   *
   * @command route:test
   * @aliases route-test
   * @bootstrap DRUSH_BOOTSTRAP_DRUPAL_FULL
   */
  public function routeTest(array $options = ['route-name' => null, 'response-code' => null, 'response-cache' => null, 'profile' => null, 'cookie' => null, 'verify-ssl' => null, 'follow-redirects' => null]) {
    /** @var \Drupal\drupen\Drupen $drupen **/
    $drupen = \Drupal::service('drupen.drupen');
    $drupen->routeTest($options, $this->io(), 'dt');
  }

  /**
   * Output a session cookie.
   *
   * @param $username
   *   Username for login.
   * @param $password
   *   Password for login.
   *
   * @command session:cookie
   * @aliases session-cookie
   * @bootstrap DRUSH_BOOTSTRAP_DRUPAL_FULL
   */
  public function sessionCookie($username, $password) {
    /** @var \Drupal\drupen\Drupen $drupen **/
    $drupen = \Drupal::service('drupen.drupen');
    $drupen->sessionCookie($username, $password, $this->io(), 'dt');
  }

}
