<?php

namespace Drupal\drupen;

use Drupal\drupen\Utils\UrlLoader;
use Drupal\drupen\Utils\Utils;
use Drupal\drupen\Utils\DrupenBatch;
use GuzzleHttp\Cookie\CookieJar;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\RouteCollection;

class Drupen {

  /**
   * List all route entries as valid urls.
   *
   * @param array $options An associative array of options whose values come from cli, aliases, config, etc.
   */
  public function routeList(array $options = ['route-name' => null]) {
    /** @var \Drupal\drupen\Utils\DrupenIo $drupenIo */
    $drupenIo = \Drupal::service('drupen.io');
    $drupenIo->io()->text($drupenIo->t('Listing all routes...'));
    $route_name = $options['route-name'] ?: FALSE;
    foreach ($this->buildRouteList($route_name) as $url) {
      $drupenIo->io()->text($drupenIo->t($url));
    }
  }

  /**
   * Test access to all route entries.
   *
   * @param array $options An associative array of options whose values come from cli, aliases, config, etc.
   */
  public function routeTest(array $options = ['route-name' => null, 'response-code' => null, 'response-cache' => null, 'profile' => null, 'cookie' => null, 'verify-ssl' => null, 'follow-redirects' => null]) {
    /** @var \Drupal\drupen\Utils\DrupenIo $drupenIo */
    $batch_size = 10;
    $route_list = array();
    foreach ($this->buildRouteList($options['route-name']) as $url) {
      array_push($route_list, $url);
    }
    $chunks = array_chunk($route_list, $batch_size);
    $total = count($route_list);
    $progress = 0;

    $drupenIo = \Drupal::service('drupen.io');
    if ($options['response-code']) {
      $drupenIo->io()->text($drupenIo->t('Testing ' . $total . ' routes for \'@code\' HTTP response code.', ['@code' => $options['response-code']]));
    }
    else {
      $drupenIo->io()->text($drupenIo->t('Testing all ' . $total . ' routes.'));
    }

    foreach ($chunks as $chunk) {
      $progress += count($chunk);
      $operations[] = array(['Drupal\drupen\Utils\DrupenBatch',_drush_bg_callback_process_route_batch], array(
        $chunk,
        dt('@percent% (Processing @progress of @total)', array(
          '@percent' => round(100 * $progress / $total),
          '@progress' => $progress,
          '@total' => $total,
        )),$options),
      );
    }
    $batch = array(
      'operations' => $operations,
      'title' => dt('Route process callback batch'),
      'finished' => ['Drupal\drupen\Utils\DrupenBatch',_drush_bg_callback_process_route_batch_finished],
      'progress_message' => dt('@progress routes of @total were processed.'),
    );
    batch_set($batch);
    drush_backend_batch_process();
  }

  /**
   * Output a session cookie.
   *
   * @param $username
   *   Username for login.
   * @param $password
   *   Password for login.
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
            /** @var \Drupal\drupen\Utils\DrupenIo $drupenIo */
            $drupenIo = \Drupal::service('drupen.io');
            $drupenIo->io()->text($drupenIo->t($response->getHeader('Set-Cookie')[0]));
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
