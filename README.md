# Behat Progress Fail Output Extension
Behat output formatter to show progress as TAP and fails inline.

[![CircleCI](https://circleci.com/gh/drevops/behat-format-progress-fail.svg?style=shield)](https://circleci.com/gh/drevops/behat-format-progress-fail)
[![Latest Stable Version](https://poser.pugx.org/drevops/behat-format-progress-fail/v/stable)](https://packagist.org/packages/drevops/behat-format-progress-fail)
[![Total Downloads](https://poser.pugx.org/drevops/behat-format-progress-fail/downloads)](https://packagist.org/packages/drevops/behat-format-progress-fail)
[![License](https://poser.pugx.org/drevops/behat-format-progress-fail/license)](https://packagist.org/packages/drevops/behat-format-progress-fail)

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
composer require --dev drevops/behat-format-progress-fail
```

## Configure

>behat.yml
```yaml
default:
  extensions:
    DrevOps\BehatFormatProgressFail\FormatExtension: ~
```
## Usage

```bash
vendor/bin/behat --format=progress_fail
```

## Maintenance

### Local development setup

1. Install Docker.
2. Start environment: `docker-compose up -d --build`.
3. Install dependencies: `docker-compose exec phpserver composer install --ansi --no-suggest`.

### Lint code

```bash
docker-compose exec phpserver vendor/bin/phpcs
```

### Run tests

```bash
docker-compose exec phpserver vendor/bin/behat
```

### Enable Xdebug

```bash
XDEBUG_ENABLE=true docker-compose up -d phpserver
```

To disable, run

```bash
docker-compose up -d phpserver
```
