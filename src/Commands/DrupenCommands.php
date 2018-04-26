<?php

namespace Drupal\drupen\Commands;

use Drush\Commands\DrushCommands;

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
   * List all route entries as valid urls.
   *
    * @param array $options An associative array of options whose values come from cli, aliases, config, etc.
   * @option route-name
   *   Filter by a single route.
   *
   * @command route:list
   * @aliases route-list
   */
  public function list(array $options = ['route-name' => null]) {
    // See bottom of https://weitzman.github.io/blog/port-to-drush9 for details on what to change when porting a
    // legacy command.
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
   */
  public function test(array $options = ['route-name' => null, 'response-code' => null, 'response-cache' => null, 'profile' => null, 'cookie' => null, 'verify-ssl' => null, 'follow-redirects' => null]) {
    // See bottom of https://weitzman.github.io/blog/port-to-drush9 for details on what to change when porting a
    // legacy command.
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
   */
  public function cookie($username, $password) {
    // See bottom of https://weitzman.github.io/blog/port-to-drush9 for details on what to change when porting a
    // legacy command.
  }

}
