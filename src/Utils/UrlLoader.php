<?php

namespace Drupal\drupen\Utils;

use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;
use GuzzleHttp\TransferStats;

class UrlLoader {

  protected $cookie;

  protected $responseCode;

  protected $responseCache;

  protected $profile;

  protected $verifySsl;

  protected $followRedirects;

  function __construct($cookie, $responseCode, $responseCache, $profile, $verifySsl, $followRedirects) {
    $this->cookie = $cookie;
    $this->responseCode = $responseCode;
    $this->responseCache = $responseCache;
    $this->profile = $profile;
    $this->verifySsl = $verifySsl;
    $this->followRedirects = $followRedirects;
  }

  /**
   * Guzzle wrapper to load and display urls.
   *
   * @param $url
   */
  public function loadURL($url) {
    if (drush_get_context('DRUSH_VERBOSE')) {
      /** @var \Drupal\drupen\Utils\DrupenIo $drupenIo */
      $drupenIo = \Drupal::service('drupen.io');
      $drupenIo->io()->text($drupenIo->t('HTTP Request: @url.', ['@url' => $url]));
    }

    /** @var \GuzzleHttp\Client $client */
    $client = \Drupal::service('http_client');

    // Use a specific cookie jar
    $jar = new CookieJar();

    if ($this->cookie) {
      $newCookie = SetCookie::fromString($this->cookie);
      /**
       * You can also do things such as $newCookie->setSecure(false);
       */
      $jar->setCookie($newCookie);
    }

    $client->request('GET', $url, [
      'on_stats' => function (TransferStats $stats) {
        if ($stats->hasResponse()) {
          $code = $stats->getResponse()->getStatusCode();
          $time = $stats->getTransferTime();
          $url = $stats->getEffectiveUri();
          if (null !== $stats->getResponse()->getHeader('X-Drupal-Cache')[0]) {
            $cache = $stats->getResponse()->getHeader('X-Drupal-Cache')[0];
          }
          else {
            $cache = 'MISS';
          }

          // Filter out based on response code.
          if ($this->responseCode && $this->responseCode != $code) {
            return;
          }

          // Filter out based on cache hit.
          if ($this->responseCache && $this->responseCache != $cache) {
            return;
          }

          if ($this->profile) {
            /** @var \Drupal\drupen\Utils\DrupenIo $drupenIo */
            $drupenIo = \Drupal::service('drupen.io');
            $drupenIo->io()->text($drupenIo->t('@code, @time, @cache, @url', [
              '@code' => $code,
              '@time' => $time,
              '@cache' => $cache,
              '@url' => $url,
            ]));
          }
          else {
            /** @var \Drupal\drupen\Utils\DrupenIo $drupenIo */
            $drupenIo = \Drupal::service('drupen.io');
            $drupenIo->io()->text($drupenIo->t('@code, @url', [
              '@code' => $code,
              '@url' => $url,
            ]));
          }
        }
      },
      'http_errors' => false,
      'cookies' => $jar,
      'curl' => [CURLOPT_SSL_VERIFYPEER => $this->verifySsl],
      'allow_redirects' => $this->followRedirects,
    ]);
  }

}
