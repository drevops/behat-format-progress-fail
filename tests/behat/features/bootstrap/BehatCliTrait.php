<?php

/**
 * @file
 * Trait to test Behat script by using Behat cli.
 *
 * phpcs:disable Drupal.Commenting.DocComment.MissingShort
 */

declare(strict_types=1);

use Behat\Behat\Hook\Scope\AfterScenarioScope;

/**
 * Trait BehatCliTrait.
 *
 * Additional shortcut steps for BehatCliContext.
 */
trait BehatCliTrait {

  /**
   * @AfterScenario
   */
  public function behatCliAfterScenarioPrintOutput(AfterScenarioScope $scope): void {
    if ($scope->getFeature()->hasTag('behatcli') && static::behatCliIsDebug()) {
      print "-------------------- OUTPUT START --------------------" . PHP_EOL;
      print PHP_EOL;
      print $this->getOutput();
      print PHP_EOL;
      print "-------------------- OUTPUT FINISH -------------------" . PHP_EOL;
    }
  }

  /**
   * Helper to print file comments.
   */
  protected static function behatCliPrintFileContents(string $filename, $title = '') {
    if (!is_readable($filename)) {
      throw new \RuntimeException(sprintf('Unable to access file "%s"', $filename));
    }

    $content = file_get_contents($filename);

    print sprintf('-------------------- %s START --------------------', $title) . PHP_EOL;
    print $filename . PHP_EOL;
    print_r($content);
    print PHP_EOL;
    print sprintf('-------------------- %s FINISH --------------------', $title) . PHP_EOL;
  }

  /**
   * Helper to check if debug mode is enabled.
   *
   * @return bool
   *   TRUE to see debug messages for this trait.
   */
  protected static function behatCliIsDebug(): string|false {
    return getenv('BEHAT_CLI_DEBUG');
  }

}
