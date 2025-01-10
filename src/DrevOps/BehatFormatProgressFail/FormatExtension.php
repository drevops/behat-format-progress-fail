<?php

declare(strict_types=1);

namespace DrevOps\BehatFormatProgressFail;

use Behat\Behat\Output\Node\EventListener\AST\StepListener;
use Behat\Config\Formatter\ShowOutputOption;
use DrevOps\BehatFormatProgressFail\Printer\PrinterProgressFail;
use Behat\Testwork\Output\NodeEventListeningFormatter;
use Behat\Testwork\Output\Node\EventListener\ChainEventListener;
use Behat\Behat\Output\Node\EventListener\Statistics\StatisticsListener;
use Behat\Behat\Output\Node\EventListener\Statistics\ScenarioStatsListener;
use Behat\Behat\Output\Node\EventListener\Statistics\StepStatsListener;
use Behat\Behat\Output\Node\EventListener\Statistics\HookStatsListener;
use Behat\Testwork\Output\Printer\StreamOutputPrinter;
use Behat\Behat\Output\Printer\ConsoleOutputFactory;
use Behat\Testwork\Exception\ServiceContainer\ExceptionExtension;
use Behat\Testwork\Output\ServiceContainer\OutputExtension;
use Behat\Testwork\ServiceContainer\Extension as ExtensionInterface;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class FormatExtension.
 *
 * Behat output formatter to show progress as TAP and failures inline.
 */
class FormatExtension implements ExtensionInterface {

  /**
   * Available services.
   */
  const ROOT_LISTENER_ID = 'output.node.listener.progress_fail';

  const RESULT_TO_STRING_CONVERTER_ID = 'output.node.printer.result_to_string';

  /**
   * Extension configuration ID.
   */
  const MOD_ID = 'progress_fail';

  /**
   * Default base path.
   */
  const BASE_PATH = '%paths.base%';

  /**
   * {@inheritdoc}
   */
  public function process(ContainerBuilder $container): void {
  }

  /**
   * {@inheritdoc}
   */
  public function getConfigKey() {
    return self::MOD_ID;
  }

  /**
   * {@inheritdoc}
   */
  public function initialize(ExtensionManager $extensionManager): void {
  }

  /**
   * {@inheritdoc}
   */
  public function configure(ArrayNodeDefinition $builder): void {
    $builder->children()->scalarNode('name')->defaultValue(self::MOD_ID);
    $builder->children()->scalarNode('base_path')->defaultValue(self::BASE_PATH);
    $builder->children()->scalarNode(ShowOutputOption::OPTION_NAME)->defaultValue(ShowOutputOption::InSummary->value);
  }

  /**
   * {@inheritdoc}
   */
  public function load(ContainerBuilder $container, array $config): void {
    $name = is_string($config['name']) ? $config['name'] : self::MOD_ID;

    $definition = new Definition(StepListener::class, [
      new Reference('output.printer.' . $name),
    ]);
    $container->setDefinition(self::ROOT_LISTENER_ID, $definition);

    $definition = new Definition(PrinterProgressFail::class, [
      new Reference(self::RESULT_TO_STRING_CONVERTER_ID),
      $config['base_path'],
    ]);
    $container->setDefinition('output.printer.' . $name, $definition);

    $definition = new Definition(NodeEventListeningFormatter::class, [
      $config['name'],
      'Prints one character per step and fail view pretty.',
      [
        'timer' => TRUE,
        ShowOutputOption::OPTION_NAME => $config[ShowOutputOption::OPTION_NAME],
      ],
      $this->createOutputPrinterDefinition(),
      new Definition(ChainEventListener::class, [
        [
          new Reference(self::ROOT_LISTENER_ID),
          new Definition(StatisticsListener::class, [
            new Reference('output.progress.statistics'),
            new Reference('output.node.printer.progress.statistics'),
          ]),
          new Definition(ScenarioStatsListener::class, [
            new Reference('output.progress.statistics'),
          ]),
          new Definition(StepStatsListener::class, [
            new Reference('output.progress.statistics'),
            new Reference(ExceptionExtension::PRESENTER_ID),
          ]),
          new Definition(HookStatsListener::class, [
            new Reference('output.progress.statistics'),
            new Reference(ExceptionExtension::PRESENTER_ID),
          ]),
        ],
      ]),
    ]);

    $definition->addTag(OutputExtension::FORMATTER_TAG, ['priority' => 100]);

    $container->setDefinition(OutputExtension::FORMATTER_TAG . '.' . $name, $definition);
  }

  /**
   * Creates output printer definition.
   *
   * @return \Symfony\Component\DependencyInjection\Definition
   *   The output printer definition.
   */
  protected function createOutputPrinterDefinition(): Definition {
    return new Definition(StreamOutputPrinter::class, [
      new Definition(ConsoleOutputFactory::class),
    ]);
  }

}
