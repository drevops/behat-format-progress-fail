<?php

/**
 * @file
 * Behat progress printer to support show fails pretty and passed like dots.
 */

namespace IntegratedExperts\BehatFormatProgressFail\Printer;

use Behat\Behat\Output\Node\Printer\Helper\ResultToStringConverter;
use Behat\Behat\Output\Node\Printer\StepPrinter;
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
    private $resultConverter;

    /**
     * @var integer $stepsPrinted
     */
    private $stepsPrinted = 0;

    /**
     * @var string $basePath
     */
    private $basePath;

    /**
     * Initializes printer.
     *
     * @param ResultToStringConverter $resultConverter
     * @param string                  $basePath
     */
    public function __construct(ResultToStringConverter $resultConverter, $basePath = '')
    {
        $this->resultConverter = $resultConverter;
        $this->basePath = $basePath;
    }

    /**
     * {@inheritdoc}
     */
    public function printStep(Formatter $formatter, Scenario $scenario, StepNode $step, StepResult $result)
    {
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
                $printer->write($this->printFailure($result, $scenario, $step));
                break;
        }

        if (++$this->stepsPrinted % 70 == 0) {
            $printer->writeln(' '.$this->stepsPrinted);
        }
    }

    /**
     * Creates information about fail step.
     *
     * @param StepResult $result
     * @param Scenario   $scenario
     * @param StepNode   $step
     *
     * @return string
     */
    protected function printFailure($result, $scenario, $step)
    {
        $output = '';
        $style = $this->resultConverter->convertResultToString($result);
        $fileName = $this->relativizePaths($result->getCallResult()->getCall()->getFeature()->getFile());
        $fileLine = $step->getLine();

        $output .= PHP_EOL;
        $output .= "{+$style}--- FAIL ---{-$style}";
        $output .= PHP_EOL;
        $output .= sprintf("    {+$style}%s %s{-$style} {+comment}# (%s):%s{-comment}", $step->getKeyword(), $step->getText(), $fileName, $fileLine, implode(PHP_EOL, array_filter($step->getArguments())));
        $output .= PHP_EOL;
        if (count(array_filter($step->getArguments())) > 0) {
            $output .= sprintf("    {+$style}%s{-$style}", implode(PHP_EOL, array_filter($step->getArguments())));
            $output .= PHP_EOL;
        }
        $output .= sprintf("      {+$style}%s{-$style}", $result->getException()->getMessage());
        $output .= PHP_EOL;
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
    protected function relativizePaths($path)
    {
        return (!$this->basePath) ? $path : str_replace(
            $this->basePath.DIRECTORY_SEPARATOR, '', $path
        );
    }
}
