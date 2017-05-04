<?php

/**
 * @file
 * Behat progress format extension.
 */

namespace IntegratedExperts\BehatFormatProgressFail;

use Behat\Testwork\ServiceContainer\Extension as ExtensionInterface;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Behat\Testwork\Output\ServiceContainer\OutputExtension;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class FormatProgressFail.
 */
class FormatExtension implements ExtensionInterface
{
  /**
   * You can modify the container here before it is dumped to PHP code.
   *
   * @param ContainerBuilder $container
   *
   * @api
   */
  public function process(ContainerBuilder $container) {
  }

  /**
   * Returns the extension config key.
   *
   * @return string
   */
  public function getConfigKey() {
    return "progress_fail";
  }

  /**
   * Initializes other extensions.
   *
   * This method is called immediately after all extensions are activated but
   * before any extension `configure()` method is called. This allows extensions
   * to hook into the configuration of other extensions providing such an
   * extension point.
   *
   * @param ExtensionManager $extensionManager
   */
  public function initialize(ExtensionManager $extensionManager) {
  }

  /**
   * Setups configuration for the extension.
   *
   * @param ArrayNodeDefinition $builder
   */
  public function configure(ArrayNodeDefinition $builder) {
    $builder->children()->scalarNode("name")->defaultValue("progress_fail");
  }

  /**
   * Loads extension services into temporary container.
   *
   * @param ContainerBuilder $container
   * @param array $config
   */
  public function load(ContainerBuilder $container, array $config) {
    $definition = new Definition("IntegratedExperts\\BehatFormatProgressFail\\Formatter\\FormatterProgressFail", [
      new Reference('output.node.printer.result_to_string'),
      $config['name'],
      '%paths.base%'
    ]);
    $container->setDefinition("output.node.printer.progress.step", $definition);
  }
}
