Feature: behat-format-progress-fail
  Behat output formatter to show progress as TAP and fails inline.

  Background:
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\CustomSnippetAcceptingContext,
          Behat\Behat\Tester\Exception\PendingException;
      use Behat\Gherkin\Node\PyStringNode,
          Behat\Gherkin\Node\TableNode;

      class FeatureContext implements CustomSnippetAcceptingContext
      {
          private $apples = 0;
          private $parameters;

          public static function getAcceptedSnippetType() { return 'regex'; }

          public function __construct(array $parameters = array()) {
              $this->parameters = $parameters;
          }

          /**
           * @Given /^I have (\d+) apples?$/
           */
          public function iHaveApples($count) {
              $this->apples = intval($count);
          }

          /**
           * @When /^I ate (\d+) apples?$/
           */
          public function iAteApples($count) {
              $this->apples -= intval($count);
          }

          /**
           * @When /^I found (\d+) apples?$/
           */
          public function iFoundApples($count) {
              $this->apples += intval($count);
          }

          /**
           * @Then /^I should have (\d+) apples$/
           */
          public function iShouldHaveApples($count) {
              PHPUnit_Framework_Assert::assertEquals(intval($count), $this->apples);
          }

          /**
           * @Then /^context parameter "([^"]*)" should be equal to "([^"]*)"$/
           */
          public function contextParameterShouldBeEqualTo($key, $val) {
              PHPUnit_Framework_Assert::assertEquals($val, $this->parameters[$key]);
          }

          /**
           * @Given /^context parameter "([^"]*)" should be array with (\d+) elements$/
           */
          public function contextParameterShouldBeArrayWithElements($key, $count) {
              PHPUnit_Framework_Assert::assertInternalType('array', $this->parameters[$key]);
              PHPUnit_Framework_Assert::assertEquals(2, count($this->parameters[$key]));
          }
      }
      """
    And a file named "behat.yml" with:
      """
      default:
        formatters:
          progress_fail: ~
        extensions:
          IntegratedExperts\BehatFormatProgressFail\FormatExtension: ~
      """
    And a file named "features/apples.feature" with:
      """
      Feature: Apples story
        In order to eat apple
        As a little kid
        I need to have an apple in my pocket

        Background:
          Given I have 3 apples

        Scenario: I'm little hungry
          When I ate 1 apple
          Then I should have 3 apples

        Scenario: Found more apples
          When I found 5 apples
          Then I should have 8 apples

        Scenario: Found more apples
          When I found 2 apples
          Then I should have 5 apples
          And do something undefined

        Scenario Outline: Other situations
          When I ate <ate> apples
          And I found <found> apples
          Then I should have <result> apples

          Examples:
            | ate | found | result |
            | 3   | 1     | 1      |
            | 0   | 4     | 8      |
            | 2   | 2     | 3      |

        Scenario: Multilines
          Given pystring:
            '''
            some pystring
            '''
          And table:
            | col1 | col2 |
            | val1 | val2 |
      """
  Scenario: 2 formats, write first to file
    When I run "behat --no-colors -f progress_fail"
    Then it should fail with:
      """
      ..
      --- FAIL ---
          Then I should have 3 apples # (features/apples.feature):11
            Failed asserting that 2 matches expected 3.
      ------------
      ......U.......
      --- FAIL ---
          Then I should have 8 apples # (features/apples.feature):25
            Failed asserting that 7 matches expected 8.
      ------------
      .....UU

      --- Failed steps:

      001 Scenario: I'm little hungry   # features/apples.feature:9
            Then I should have 3 apples # features/apples.feature:11
              Failed asserting that 2 matches expected 3.

      002 Example: | 0   | 4     | 8      | # features/apples.feature:30
            Then I should have 8 apples     # features/apples.feature:25
              Failed asserting that 7 matches expected 8.

      7 scenarios (3 passed, 2 failed, 2 undefined)
      25 steps (20 passed, 2 failed, 3 undefined)

      --- FeatureContext has missing steps. Define them with these snippets:

          /**
           * @Then /^do something undefined$/
           */
          public function doSomethingUndefined()
          {
              throw new PendingException();
          }

          /**
           * @Given /^pystring:$/
           */
          public function pystring(PyStringNode $string)
          {
              throw new PendingException();
          }

          /**
           * @Given /^table:$/
           */
          public function table(TableNode $table)
          {
              throw new PendingException();
          }
      """
