# behat-format-progress-fail
Behat output formatter to show progress as TAP and fails inline.

[![CircleCI](https://circleci.com/gh/integratedexperts/behat-format-progress-fail.svg?style=shield)](https://circleci.com/gh/integratedexperts/behat-format-progress-fail)
[![Latest Stable Version](https://poser.pugx.org/integratedexperts/behat-format-progress-fail/v/stable)](https://packagist.org/packages/integratedexperts/behat-format-progress-fail)
[![Total Downloads](https://poser.pugx.org/integratedexperts/behat-format-progress-fail/downloads)](https://packagist.org/packages/integratedexperts/behat-format-progress-fail)
[![License](https://poser.pugx.org/integratedexperts/behat-format-progress-fail/license)](https://packagist.org/packages/integratedexperts/behat-format-progress-fail)

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
>Mac OS/Linux terminal
```bash
vendor/bin/behat --format=progress_fail
```
