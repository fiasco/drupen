<?php

namespace Drupal\drupen\Commands;

use Drush\Commands\DrushCommands;
use Symfony\Component\Routing\RouteCollection;

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
  public function routeList(array $options = ['route-name' => null]) {
    $this->output()->writeln('Listing all routes...');
    $route_name = $options['route-name'] ?: FALSE;
    foreach ($this->buildRouteList($route_name) as $url) {
      $this->output()->writeln($url);
    }
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
  public function routeTest(array $options = ['route-name' => null, 'response-code' => null, 'response-cache' => null, 'profile' => null, 'cookie' => null, 'verify-ssl' => null, 'follow-redirects' => null]) {
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
  public function sessionCookie($username, $password) {
    // See bottom of https://weitzman.github.io/blog/port-to-drush9 for details on what to change when porting a
    // legacy command.
  }

  /**
   * Build a list of routes with replacement parameters.
   *
   * @return \Generator
   */
  protected function buildRouteList($route_name = FALSE) {
    $route_handlers = \Drupal::service('drupen.route.handler.manager')->getHandlers();
    $collections = [];
    $routes = [];

    /** @var \Drupal\Core\Routing\RouteProvider $route_provider */
    $route_provider = \Drupal::service('router.route_provider');
    if ($route_name) {
      try {
        $routes[$route_name] = $route_provider->getRouteByName($route_name);
      }
      catch (RouteNotFoundException $e) {
        drush_set_error('route_mismatch', $e->getMessage());
        yield;
      }
    }
    else {
      $routes = $route_provider->getAllRoutes();
    }

    /** @var \Symfony\Component\Routing\Route $route */
    foreach ($routes as $route_name => $route) {
      /** @var \Drupal\drupen\RouteHandler\RouteHandlerInterface $route_handler */
      foreach ($route_handlers as $handler_name => $route_handler) {
        if ($route_handler->applies($route)) {
          if (empty($collections[$handler_name])) {
            $collections[$handler_name] = new RouteCollection();
          }
          $collections[$handler_name]->add($route_name, $route);
          break;
        }
      }
    }

    foreach ($collections as $handler_name => $collection) {
      $route_handler = $route_handlers[$handler_name];
      foreach ($route_handler->getUrls($collection) as $url) {
        if (!$url) {
          continue;
        }
        yield $url;
      }
    }
  }

}
