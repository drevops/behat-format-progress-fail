<?php

use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Symfony\Component\Finder\Finder;

/**
 * Trait BehatCliTrait.
 *
 * Additional shortcut steps for BehatCliContext.
 */
trait BehatCliTrait
{

    /**
     * @AfterScenario
     */
    public function behatCliAfterScenarioPrintOutput(AfterScenarioScope $scope)
    {
        if ($scope->getFeature()->hasTag('behatcli')) {
            if (static::behatCliIsDebug()) {
                print "-------------------- OUTPUT START --------------------".PHP_EOL;
                print PHP_EOL;
                print $this->getOutput();
                print PHP_EOL;
                print "-------------------- OUTPUT FINISH -------------------".PHP_EOL;
            }
        }
    }

    /**
     * Helper to print file comments.
     */
    protected static function behatCliPrintFileContents($filename, $title = '')
    {
        if (!is_readable($filename)) {
            throw new \RuntimeException(sprintf('Unable to access file "%s"', $filename));
        }

        $content = file_get_contents($filename);

        print "-------------------- $title START --------------------".PHP_EOL;
        print $filename.PHP_EOL;
        print_r($content);
        print PHP_EOL;
        print "-------------------- $title FINISH --------------------".PHP_EOL;
    }

    /**
     * Helper to check if debug mode is enabled.
     *
     * @return bool
     *   TRUE to see debug messages for this trait.
     */
    protected static function behatCliIsDebug()
    {
        return getenv('BEHAT_CLI_DEBUG');
    }
}
