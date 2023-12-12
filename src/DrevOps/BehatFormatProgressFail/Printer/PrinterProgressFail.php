<?php

/**
 * @file
 * Behat progress printer to support show fails pretty and passed like dots.
 */

namespace DrevOps\BehatFormatProgressFail\Printer;

use Behat\Behat\Definition\Call\DefinitionCall;
use Behat\Behat\Output\Node\Printer\Helper\ResultToStringConverter;
use Behat\Behat\Output\Node\Printer\StepPrinter;
use Behat\Behat\Tester\Result\ExecutedStepResult;
use Behat\Behat\Tester\Result\StepResult;
use Behat\Gherkin\Node\ScenarioLikeInterface as Scenario;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Tester\Result\TestResult;

/**
 * Class FormatProgressFail.
 */
class PrinterProgressFail implements StepPrinter
{

    /**
     * @var ResultToStringConverter $resultConverter
     */
    private ResultToStringConverter $resultConverter;

    /**
     * @var int $stepsPrinted
     */
    private int $stepsPrinted = 0;

    /**
     * @var string $basePath
     */
    private string $basePath;

    /**
     * Initializes printer.
     *
     * @param ResultToStringConverter $resultConverter
     * @param string                  $basePath
     */
    public function __construct(ResultToStringConverter $resultConverter, string $basePath = '')
    {
        $this->resultConverter = $resultConverter;
        $this->basePath = $basePath;
    }

    /**
     * {@inheritdoc}
     */
    public function printStep(Formatter $formatter, Scenario $scenario, StepNode $step, StepResult $result): void
    {
        $lineWidth = 70;
        $printer = $formatter->getOutputPrinter();
        $style = $this->resultConverter->convertResultToString($result);

        switch ($result->getResultCode()) {
            case TestResult::PASSED:
                $printer->write("{+$style}.{-$style}");
                break;
            case TestResult::SKIPPED:
                $printer->write("{+$style}-{-$style}");
                break;
            case TestResult::PENDING:
                $printer->write("{+$style}P{-$style}");
                break;
            case StepResult::UNDEFINED:
                $printer->write("{+$style}U{-$style}");
                break;
            case TestResult::FAILED:
                $printer->write($this->printFailure($result, $step));
                break;
        }

        if (0 === ++$this->stepsPrinted % $lineWidth) {
            $printer->writeln(' '.$this->stepsPrinted);
        }
    }

    /**
     * Creates information about fail step.
     *
     * @param StepResult $result
     * @param StepNode   $step
     *
     * @return string
     */
    protected function printFailure(StepResult $result, StepNode $step): string
    {
        $style = $this->resultConverter->convertResultToString($result);

        // Return default format for any non-executed step results.
        if (!$result instanceof ExecutedStepResult) {
            return "{+$style}F{-$style}";
        }

        $output = '';

        $fileName = '';
        $callResult = $result->getCallResult();
        $call = $callResult->getCall();
        if ($call instanceof DefinitionCall) {
            $feature = $call->getFeature();
            $fileName = $this->relativizePaths($feature->getFile() ?? '');
        }
        $fileLine = $step->getLine();

        $output .= PHP_EOL;
        $output .= "{+$style}--- FAIL ---{-$style}";
        $output .= PHP_EOL;

        $output .= sprintf("    {+$style}%s %s{-$style} {+comment}# (%s):%s{-comment}", $step->getKeyword(), $step->getText(), $fileName, $fileLine);
        $output .= PHP_EOL;

        $stepArguments = $step->getArguments();
        $stepArguments = array_map(function ($item) {
            if (method_exists($item, '__toString')) {
                return $item->__toString();
            }

            return '';
        }, $stepArguments);
        $stepArguments = array_filter($stepArguments);
        if (count($stepArguments) > 0) {
            $output .= sprintf("    {+$style}%s{-$style}", implode(PHP_EOL, array_filter($stepArguments)));
            $output .= PHP_EOL;
        }

        $exception = $result->getException();
        if ($exception) {
            $output .= sprintf("      {+$style}%s{-$style}", $exception->getMessage());
            $output .= PHP_EOL;
        }

        $output .= "{+$style}------------{-$style}";
        $output .= PHP_EOL;

        return sprintf("%s", $output);
    }

    /**
     * Transforms path to relative.
     *
     * @param string $path
     *
     * @return string
     */
    protected function relativizePaths(string $path): string
    {
        return !$this->basePath ? $path : str_replace(
            $this->basePath.DIRECTORY_SEPARATOR, '', $path
        );
    }
}
