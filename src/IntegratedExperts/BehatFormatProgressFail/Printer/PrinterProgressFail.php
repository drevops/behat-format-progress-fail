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
use Behat\Behat\Output\Statistics\Statistics;

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
    public function printStep(
        Formatter $formatter,
        Scenario $scenario,
        StepNode $step,
        StepResult $result
    ) {
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
                $fileName = $this->relativizePaths($result->getCallResult()->getCall()->getFeature()->getFile());
                $fileLine = $scenario->getLine();
                $testLine = sprintf("(%s):%s", $fileName, $fileLine);
                $scenarioTitle = sprintf("%s:\n    %s", $scenario->getKeyword(), $scenario->getTitle());
                $stepInfo = sprintf("      %s %s", $step->getKeyword(), $step->getText());
                $errorText = "";
                foreach ($step->getArguments() as $argument) {
                    $errorText = (empty($argument)) ? "\n" : $argument;
                }
                $printer->write(
                    sprintf("\n---{+$style} FAIL {-$style}---\n---{+$style} %s\n%s\n%s\n%s{-$style}\n", $testLine, $scenarioTitle, $stepInfo, $errorText)
                );
                break;
        }

        if (++$this->stepsPrinted % 70 == 0) {
            $printer->writeln(' '.$this->stepsPrinted);
        }
    }

  /**
   * Transforms path to relative.
   *
   * @param string $path
   *
   * @return string
   */
    private function relativizePaths($path)
    {
        return (!$this->basePath) ? $path : str_replace($this->basePath.DIRECTORY_SEPARATOR, '', $path);
    }
}
