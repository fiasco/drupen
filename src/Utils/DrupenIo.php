<?php

namespace Drupal\drupen\Utils;

class DrupenIo {

  /**
   * The io interface of the cli tool calling the method.
   *
   * @var \Symfony\Component\Console\Style\StyleInterface|\DrupenDrush8Io
   */
  protected $io;

  /**
   * The translation function akin to t().
   *
   * @var callable
   */
  protected $translation_function;

  /**
   * @return \DrupenDrush8Io|\Symfony\Component\Console\Style\StyleInterface
   */
  public function io() {
    return $this->io;
  }

  /**
   * @return callable
   */
  public function t($string, $args = []) {
    $translation_function = $this->translation_function;
    return $translation_function($string, $args);
  }

  /**
   * @param \Symfony\Component\Console\Style\StyleInterface|\DrupenDrush8Io $io
   *   The io interface of the cli tool calling the method.
   * @param callable $t
   *   The translation function akin to t().
   */
  public function setupIo($io, callable $t) {
    $this->io = $io;
    $this->translation_function = $t;
  }

}
