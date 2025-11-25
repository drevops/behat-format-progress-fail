Feature: Format

  Assert that the output format work as expected.

  Background:
    Given a file named "features/bootstrap/FeatureContextTest.php" with:
      """
      <?php

      use Behat\Behat\Context\CustomSnippetAcceptingContext,
          Behat\Behat\Tester\Exception\PendingException;
      use Behat\Gherkinode\PyStringNode,
          Behat\Gherkinode\TableNode;
      use PHPUnit\Framework\Assert;

      class FeatureContextTest implements CustomSnippetAcceptingContext
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
              Assert::assertEquals(intval($count), $this->apples);
          }

          /**
           * @Then /^I should have (\d+) apples verbose$/
           */
          public function iShouldHaveApplesVerbose($count) {
              print "I show you $this->apples apples";
              Assert::assertEquals(intval($count), $this->apples);
          }

          /**
           * @Then /^context parameter "([^"]*)" should be equal to "([^"]*)"$/
           */
          public function contextParameterShouldBeEqualTo($key, $val) {
              Assert::assertEquals($val, $this->parameters[$key]);
          }

          /**
           * @Given /^context parameter "([^"]*)" should be array with (\d+) elements$/
           */
          public function contextParameterShouldBeArrayWithElements($key, $count) {
              Assert::assertInternalType('array', $this->parameters[$key]);
              Assert::assertEquals(2, count($this->parameters[$key]));
          }
      }
      """

  Scenario: Failures during the test formatted correctly
    Given a file named "behat.yml" with:
      """
      default:
        suites:
          default:
            contexts:
              - FeatureContextTest
        formatters:
          progress_fail: ~
        extensions:
          DrevOps\BehatFormatProgressFail\FormatExtension: ~
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
    When I run "behat --no-colors --strict -f progress_fail"
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

      --- FeatureContextTest has missing steps. Define them with these snippets:

          #[Then('/^do something undefined$/')]
          public function doSomethingUndefined(): void
          {
              throw new PendingException();
          }

          #[Given('/^pystring:$/')]
          public function pystring(PyStringNode $string): void
          {
              throw new PendingException();
          }

          #[Given('/^table:$/')]
          public function table(TableNode $table): void
          {
              throw new PendingException();
          }
      """

  Scenario: Output should be shown only for failed steps
    Given a file named "behat.yml" with:
      """
      default:
        suites:
          default:
            contexts:
              - FeatureContextTest
        formatters:
          progress_fail:
            show_output: on-fail
        extensions:
          DrevOps\BehatFormatProgressFail\FormatExtension: ~
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
          Then I should have 3 apples verbose
      """
    When I run "behat --no-colors --strict -f progress_fail"
    Then it should fail with:
      """
      ..
      --- FAIL ---
          Then I should have 3 apples verbose # (features/apples.feature):11
            Failed asserting that 2 matches expected 3.
      ------------

      FeatureContextTest::iShouldHaveApplesVerbose():
        | I show you 2 apples

      --- Failed steps:

      001 Scenario: I'm little hungry           # features/apples.feature:9
            Then I should have 3 apples verbose # features/apples.feature:11
              │ I show you 2 apples
              Failed asserting that 2 matches expected 3.

      1 scenario (1 failed)
      3 steps (2 passed, 1 failed)
      """

  Scenario: Output should always be shown
    Given a file named "behat.yml" with:
      """
      default:
        suites:
          default:
            contexts:
              - FeatureContextTest
        formatters:
          progress_fail:
            show_output: yes
        extensions:
          DrevOps\BehatFormatProgressFail\FormatExtension: ~
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
          Then I should have 2 apples verbose
          Then I should have 3 apples
      """
    When I run "behat --no-colors --strict -f progress_fail"
    Then it should fail with:
      """
      ...
      FeatureContextTest::iShouldHaveApplesVerbose():
        | I show you 2 apples
      --- FAIL ---
          Then I should have 3 apples # (features/apples.feature):12
            Failed asserting that 2 matches expected 3.
      ------------


      --- Failed steps:

      001 Scenario: I'm little hungry   # features/apples.feature:9
            Then I should have 3 apples # features/apples.feature:12
              Failed asserting that 2 matches expected 3.

      1 scenario (1 failed)
      4 steps (3 passed, 1 failed)
      """

  Scenario: Output should not be shown only when not allowed
    Given a file named "behat.yml" with:
      """
      default:
        suites:
          default:
            contexts:
              - FeatureContextTest
        formatters:
          progress_fail:
            show_output: no
        extensions:
          DrevOps\BehatFormatProgressFail\FormatExtension: ~
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
          Then I should have 3 apples verbose
      """
    When I run "behat --no-colors --strict -f progress_fail"
    Then it should fail with:
      """
      ..
      --- FAIL ---
          Then I should have 3 apples verbose # (features/apples.feature):11
            Failed asserting that 2 matches expected 3.
      ------------


      --- Failed steps:

      001 Scenario: I'm little hungry           # features/apples.feature:9
            Then I should have 3 apples verbose # features/apples.feature:11
              Failed asserting that 2 matches expected 3.

      1 scenario (1 failed)
      3 steps (2 passed, 1 failed)
      """

  Scenario: Metrics should not double when used with multiple formatters
    Given a file named "behat.yml" with:
      """
      default:
        suites:
          default:
            contexts:
              - FeatureContextTest
        extensions:
          DrevOps\BehatFormatProgressFail\FormatExtension: ~
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
          Then I should have 2 apples
          And I should have 2 apples

        Scenario: I eat wrong amount
          When I ate 1 apple
          Then I should have 3 apples
          And I should have 2 apples

        Scenario: I found apples
          When I found 2 apples
          Then I should have 5 apples
          And I should have 5 apples
      """
    When I run "behat --no-colors --strict -f progress -o progress.txt -f progress_fail -o std"
    Then it should fail with:
      """
      ......
      --- FAIL ---
          Then I should have 3 apples # (features/apples.feature):16
            Failed asserting that 2 matches expected 3.
      ------------
      -....

      --- Failed steps:

      001 Scenario: I eat wrong amount  # features/apples.feature:14
            Then I should have 3 apples # features/apples.feature:16
              Failed asserting that 2 matches expected 3.

      3 scenarios (2 passed, 1 failed)
      12 steps (10 passed, 1 failed, 1 skipped)
      """

