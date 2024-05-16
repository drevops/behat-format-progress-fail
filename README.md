<p align="center">
  <a href="" rel="noopener">
  <img width=200px height=200px src="https://placehold.jp/000000/ffffff/200x200.png?text=Behat+Progress+Fail+Output&css=%7B%22border-radius%22%3A%22%20100px%22%7D" alt="Behat Progress Fail Output logo"></a>
</p>

<h1 align="center">Behat Progress Fail Output Extension</h1>

<div align="center">

[![GitHub Issues](https://img.shields.io/github/issues/drevops/behat-format-progress-fail.svg)](https://github.com/drevops/behat-format-progress-fail/issues)
[![GitHub Pull Requests](https://img.shields.io/github/issues-pr/drevops/behat-format-progress-fail.svg)](https://github.com/drevops/behat-format-progress-fail/pulls)
[![Test](https://github.com/drevops/behat-format-progress-fail/actions/workflows/test-php.yml/badge.svg)](https://github.com/drevops/behat-format-progress-fail/actions/workflows/test-php.yml)
![GitHub release (latest by date)](https://img.shields.io/github/v/release/drevops/behat-format-progress-fail)
![LICENSE](https://img.shields.io/github/license/drevops/behat-format-progress-fail)
![Renovate](https://img.shields.io/badge/renovate-enabled-green?logo=renovatebot)

</div>

<p align="center">Behat output formatter to show progress as TAP and failures inline.
    <br>
</p>

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

## Installation

```bash
composer require --dev drevops/behat-format-progress-fail
```

## Usage

```bash
vendor/bin/behat --format=progress_fail
```

### Configure

>behat.yml
```yaml
default:
  extensions:
    DrevOps\BehatFormatProgressFail\FormatExtension: ~
```

## Maintenance

### Lint code

```bash
composer lint
composer lint-fix
```

### Run tests

```bash
composer test
```

---
Repository created using https://getscaffold.dev/ project scaffold template

