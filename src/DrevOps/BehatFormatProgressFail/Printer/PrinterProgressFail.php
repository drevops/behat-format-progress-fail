<?php

declare(strict_types=1);

namespace DrevOps\BehatFormatProgressFail\Printer;

use Behat\Behat\Definition\Call\DefinitionCall;
use Behat\Behat\Output\Node\Printer\Helper\ResultToStringConverter;
use Behat\Behat\Output\Node\Printer\StepPrinter;
use Behat\Behat\Tester\Result\ExecutedStepResult;
use Behat\Behat\Tester\Result\StepResult;
use Behat\Config\Formatter\ShowOutputOption;
use Behat\Gherkin\Node\ScenarioLikeInterface as Scenario;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Printer\OutputPrinter;
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
    $line_width = 70;
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

    $show_output = $formatter->getParameter(ShowOutputOption::OPTION_NAME);
    if ($show_output === ShowOutputOption::Yes ||
      ($show_output === ShowOutputOption::OnFail && !$result->isPassed())) {
      $this->printStdOut($formatter->getOutputPrinter(), $result);
    }

    if (0 === ++$this->stepsPrinted % $line_width) {
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

    $file_name = '';
    $call_result = $result->getCallResult();
    $call = $call_result->getCall();
    if ($call instanceof DefinitionCall) {
      $feature = $call->getFeature();
      $file_name = $this->relativizePaths($feature->getFile() ?? '');
    }
    $fileLine = $step->getLine();

    $output .= PHP_EOL;
    $output .= sprintf('{+%s}--- FAIL ---{-%s}', $style, $style);
    $output .= PHP_EOL;

    $output .= sprintf(sprintf('    {+%s}%%s %%s{-%s} {+comment}# (%%s):%%s{-comment}', $style, $style), $step->getKeyword(), $step->getText(), $file_name, $fileLine);
    $output .= PHP_EOL;

    $step_arguments = $step->getArguments();
    $step_arguments = array_map(static function ($item) {
      if (method_exists($item, '__toString')) {
        return $item->__toString();
      }

      return '';
    }, $step_arguments);

    $step_arguments = array_filter($step_arguments);

    if (count($step_arguments) > 0) {
      $output .= sprintf(sprintf('    {+%s}%%s{-%s}', $style, $style), implode(PHP_EOL, $step_arguments));
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
   * Prints step output (if has one).
   */
  protected function printStdOut(OutputPrinter $printer, StepResult $result): void {
    if (!$result instanceof ExecutedStepResult || NULL === $result->getCallResult()->getStdOut()) {
      return;
    }

    $step_definition = $result->getStepDefinition();
    if (!$step_definition) {
      return;
    }

    $printer->writeln("\n" . $step_definition->getPath() . ':');
    $call_result = $result->getCallResult();
    $pad = function ($line): string {
      return sprintf('  | {+stdout}%s{-stdout}', $line);
    };

    $printer->write(implode("\n", array_map($pad, explode("\n", (string) $call_result->getStdOut()))));
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
