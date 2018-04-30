<?php

namespace Drush\Commands;

use Drupal\drupen\Utils\UrlLoader;
use Drupal\drupen\Utils\Utils;
use Drush\Commands\DrushCommands;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\TransferStats;
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

  public function __construct() {
    $this->setupAutoloading();
  }

  /**
   * Setup autoloading.
   */
  protected function setupAutoloading() {
    require_once __DIR__ . '/../../vendor/autoload.php';
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
   * @bootstrap DRUSH_BOOTSTRAP_DRUPAL_FULL
   */
  public function routeTest(array $options = ['route-name' => null, 'response-code' => null, 'response-cache' => null, 'profile' => null, 'cookie' => null, 'verify-ssl' => null, 'follow-redirects' => null]) {
    if ($options['response-code']) {
      $this->output()->writeln('Testing routes for \'@code\' HTTP response code.', ['@code' => $options['response-code']]);
    }
    else {
      $this->output()->writeln('Testing all routes.');
    }

    foreach ($this->buildRouteList($options['route-name']) as $url) {
      $urlLoader = new UrlLoader($options['cookie'], $options['response-code'], $options['response-cache'], $options['profile'], $options['verify-ssl'], $options['follow-redirects']);
      $urlLoader->loadURL($url);
    }
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
    $url = Utils::renderLink('user.login');
    /** @var \GuzzleHttp\Client $client */
    $client = \Drupal::service('http_client');
    $jar = new CookieJar();

    $client->request('POST', $url,
      [
        'form_params' => [
          'name' => $username,
          'pass' => $password,
          'form_id' => 'user_login_form',
          'op' => 'Log in',
        ],
        'cookies' => $jar,
        'allow_redirects' => [
          'max'             => 5,
          'referer'         => true,
          'on_redirect'     => function($request, $response, $uri) {
            $this->output()->writeln($response->getHeader('Set-Cookie')[0]);
          },
          'track_redirects' => true
        ],
      ]
    );
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
