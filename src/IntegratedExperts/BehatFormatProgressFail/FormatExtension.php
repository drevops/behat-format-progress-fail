<?php

/**
 * @file
 * Behat progress format extension.
 */

namespace IntegratedExperts\BehatFormatProgressFail;

use Behat\Testwork\Exception\ServiceContainer\ExceptionExtension;
use Behat\Testwork\Output\ServiceContainer\OutputExtension;
use Behat\Testwork\ServiceContainer\Extension as ExtensionInterface;
use Behat\Testwork\ServiceContainer\ExtensionManager;
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
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     *
     * @api
     */
    public function process(ContainerBuilder $container)
    {
    }

    /**
     * Returns the extension config key.
     *
     * @return string
     */
    public function getConfigKey()
    {
        return self::MOD_ID;
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
    public function initialize(ExtensionManager $extensionManager)
    {
    }

    /**
     * Setups configuration for the extension.
     *
     * @param ArrayNodeDefinition $builder
     */
    public function configure(ArrayNodeDefinition $builder)
    {
        $builder->children()->scalarNode('name')->defaultValue(self::MOD_ID);
        $builder->children()->scalarNode('base_path')->defaultValue(
            self::BASE_PATH
        );
    }

    /**
     * Loads extension services into temporary container.
     *
     * @param ContainerBuilder $container
     * @param array            $config
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
            'IntegratedExperts\BehatFormatProgressFail\Printer\PrinterProgressFail', [
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
