# behat-format-progress-fail
Behat output formatter to show progress as TAP and fails inline.

[![CircleCI](https://circleci.com/gh/integratedexperts/behat-format-progress-fail.svg?style=shield)](https://circleci.com/gh/integratedexperts/behat-format-progress-fail)
[![Latest Stable Version](https://poser.pugx.org/integratedexperts/behat-format-progress-fail/v/stable)](https://packagist.org/packages/integratedexperts/behat-format-progress-fail)
[![Total Downloads](https://poser.pugx.org/integratedexperts/behat-format-progress-fail/downloads)](https://packagist.org/packages/integratedexperts/behat-format-progress-fail)
[![License](https://poser.pugx.org/integratedexperts/behat-format-progress-fail/license)](https://packagist.org/packages/integratedexperts/behat-format-progress-fail)

## Output
```
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
```

![Output in CI](https://cloud.githubusercontent.com/assets/378794/26039517/1765b812-395f-11e7-9932-dd1aa43a97d4.png)

## Installing
```bash
composer require --dev integratedexperts/behat-format-progress-fail
```

## Configure
>behat.yml
```yaml
default:
  extensions:
    IntegratedExperts\BehatFormatProgressFail\FormatExtension: ~
```
## Usage
```bash
vendor/bin/behat --format=progress_fail
```
