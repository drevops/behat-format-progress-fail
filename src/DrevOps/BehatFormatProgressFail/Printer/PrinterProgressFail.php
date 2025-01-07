<?php

declare(strict_types=1);

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
class PrinterProgressFail implements StepPrinter {

  /**
   * Number of steps printed.
   */
  protected int $stepsPrinted = 0;

  /**
   * Initializes printer.
   */
  public function __construct(private readonly ResultToStringConverter $resultConverter, private readonly string $basePath = '') {
  }

  /**
   * {@inheritdoc}
   */
  public function printStep(Formatter $formatter, Scenario $scenario, StepNode $step, StepResult $result): void {
    $lineWidth = 70;
    $printer = $formatter->getOutputPrinter();
    $style = $this->resultConverter->convertResultToString($result);

    switch ($result->getResultCode()) {
      case TestResult::PASSED:
        $printer->write(sprintf('{+%s}.{-%s}', $style, $style));
        break;

      case TestResult::SKIPPED:
        $printer->write(sprintf('{+%s}-{-%s}', $style, $style));
        break;

      case TestResult::PENDING:
        $printer->write(sprintf('{+%s}P{-%s}', $style, $style));
        break;

      case StepResult::UNDEFINED:
        $printer->write(sprintf('{+%s}U{-%s}', $style, $style));
        break;

      case TestResult::FAILED:
        $printer->write($this->printFailure($result, $step));
        break;
    }

    if (0 === ++$this->stepsPrinted % $lineWidth) {
      $printer->writeln(' ' . $this->stepsPrinted);
    }
  }

  /**
   * Creates information about fail step.
   *
   * @return string
   *   Information about fail step.
   */
  protected function printFailure(StepResult $result, StepNode $step): string {
    $style = $this->resultConverter->convertResultToString($result);

    // Return default format for any non-executed step results.
    if (!$result instanceof ExecutedStepResult) {
      return sprintf('{+%s}F{-%s}', $style, $style);
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
    $output .= sprintf('{+%s}--- FAIL ---{-%s}', $style, $style);
    $output .= PHP_EOL;

    $output .= sprintf(sprintf('    {+%s}%%s %%s{-%s} {+comment}# (%%s):%%s{-comment}', $style, $style), $step->getKeyword(), $step->getText(), $fileName, $fileLine);
    $output .= PHP_EOL;

    $stepArguments = $step->getArguments();
    $stepArguments = array_map(static function ($item) {
      if (method_exists($item, '__toString')) {
            return $item->__toString();
      }

        return '';
    }, $stepArguments);

    $stepArguments = array_filter($stepArguments);

    if (count($stepArguments) > 0) {
      $output .= sprintf(sprintf('    {+%s}%%s{-%s}', $style, $style), implode(PHP_EOL, $stepArguments));
      $output .= PHP_EOL;
    }

    $exception = $result->getException();
    if ($exception) {
      $output .= sprintf(sprintf('      {+%s}%%s{-%s}', $style, $style), $exception->getMessage());
      $output .= PHP_EOL;
    }

    $output .= sprintf('{+%s}------------{-%s}', $style, $style);

    return $output . PHP_EOL;
  }

  /**
   * Transforms path to relative.
   *
   * @return string
   *   Relative path.
   */
  protected function relativizePaths(string $path): string {
    return $this->basePath === '' || $this->basePath === '0' ? $path : str_replace(
          $this->basePath . DIRECTORY_SEPARATOR, '', $path
      );
  }

}
