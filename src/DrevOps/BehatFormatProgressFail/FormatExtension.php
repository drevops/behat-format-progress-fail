<?php

/**
 * @file
 * Behat progress format extension.
 */

namespace DrevOps\BehatFormatProgressFail;

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
 */
class FormatExtension implements ExtensionInterface
{

    /**
     * Available services
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
    public function process(ContainerBuilder $container)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigKey()
    {
        return self::MOD_ID;
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(ExtensionManager $extensionManager)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function configure(ArrayNodeDefinition $builder)
    {
        $builder->children()->scalarNode('name')->defaultValue(self::MOD_ID);
        $builder->children()->scalarNode('base_path')->defaultValue(self::BASE_PATH);
    }

    /**
     * {@inheritdoc}
     */
    public function load(ContainerBuilder $container, array $config)
    {
        $definition = new Definition(
            'Behat\Behat\Output\Node\EventListener\AST\StepListener', [
                new Reference('output.printer.'.$config['name']),
            ]
        );
        $container->setDefinition(self::ROOT_LISTENER_ID, $definition);

        $definition = new Definition(
            'DrevOps\BehatFormatProgressFail\Printer\PrinterProgressFail', [
                new Reference(self::RESULT_TO_STRING_CONVERTER_ID),
                $config['base_path'],
            ]
        );
        $container->setDefinition(
            'output.printer.'.$config['name'], $definition
        );

        $definition = new Definition(
            'Behat\Testwork\Output\NodeEventListeningFormatter', [
                $config['name'],
                'Prints one character per step and fail view pretty.',
                ['timer' => true],
                $this->createOutputPrinterDefinition(),
                new Definition(
                    'Behat\Testwork\Output\Node\EventListener\ChainEventListener', [
                        [
                            new Reference(self::ROOT_LISTENER_ID),
                            new Definition(
                                'Behat\Behat\Output\Node\EventListener\Statistics\StatisticsListener', [
                                    new Reference('output.progress.statistics'),
                                    new Reference(
                                        'output.node.printer.progress.statistics'
                                    ),
                                ]
                            ),
                            new Definition(
                                'Behat\Behat\Output\Node\EventListener\Statistics\ScenarioStatsListener', [
                                    new Reference('output.progress.statistics'),
                                ]
                            ),
                            new Definition(
                                'Behat\Behat\Output\Node\EventListener\Statistics\StepStatsListener', [
                                    new Reference('output.progress.statistics'),
                                    new Reference(
                                        ExceptionExtension::PRESENTER_ID
                                    ),
                                ]
                            ),
                            new Definition(
                                'Behat\Behat\Output\Node\EventListener\Statistics\HookStatsListener', [
                                    new Reference('output.progress.statistics'),
                                    new Reference(
                                        ExceptionExtension::PRESENTER_ID
                                    ),
                                ]
                            ),
                        ],
                    ]
                ),
            ]
        );
        $definition->addTag(
            OutputExtension::FORMATTER_TAG, ['priority' => 100]
        );
        $container->setDefinition(
            OutputExtension::FORMATTER_TAG.'.'.$config['name'], $definition
        );
    }

    /**
     * Creates output printer definition.
     *
     * @return Definition
     */
    protected function createOutputPrinterDefinition()
    {
        return new Definition(
            'Behat\Testwork\Output\Printer\StreamOutputPrinter', [
                new Definition(
                    'Behat\Behat\Output\Printer\ConsoleOutputFactory'
                ),
            ]
        );
    }
}
