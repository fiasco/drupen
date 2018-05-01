<?php

namespace Drupal\drupen;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceModifierInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class DrupenServiceProvider implements ServiceModifierInterface {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    $yaml_loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../'));
    $yaml_loader->load('drupen.services.yml');
  }

}
