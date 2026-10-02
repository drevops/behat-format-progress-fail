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

[![Vortex Ecosystem](https://img.shields.io/badge/%F0%9F%8C%80-Vortex%20Ecosystem-2C5A68?style=for-the-badge&labelColor=65ACBC)](https://github.com/drevops/vortex)
</div>

> [!WARNING]
> **This package is deprecated.** It is supported until **31 December 2026**, after which it will be marked as abandoned and will receive no further releases, fixes, or compatibility updates.
>
> Behat has done this natively since **v3.33.0**. The built-in `progress` formatter gained an `inline_failures` option that prints each failed, pending or undefined step as it happens, instead of holding everything back until the end-of-run summary. That's [exactly what this extension was built for](https://github.com/Behat/Behat/issues/1860), so it has served its purpose.
>
> **Remove this package before you upgrade Behat to 3.33.0 or newer.** [Migrating to Behat core](#migrating-to-behat-core) walks through it, including why Composer won't always catch this for you.

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

This package supports Behat 3.29 to 3.32. On Behat 3.33.0 or newer, use the built-in formatter instead - see [Migrating to Behat core](#migrating-to-behat-core).

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

or

>behat.yml
```yaml
default:
  extensions:
    DrevOps\BehatFormatProgressFail\FormatExtension:
      show_output: in-summary # Supported values: yes | no | on-fail
```

#### `show_output`

Show output from within test steps. "Output" is `print`, `echo`, `var_dump`, etc.

- `yes` - always show the output
- `no` - do not show the output
- `on-fail` - only show the output if there are test fails
- `in-summary` - only show in the summary if there are test fails

## Migrating to Behat core

Behat 3.33.0 added an `inline_failures` option to its built-in `progress` formatter, and every release since carries it, 4.x included. It covers what this extension does, so moving over comes down to 2 steps: swap the packages, then point your config at the core formatter.

### 1. Remove this package, then upgrade Behat

```bash
composer remove --dev drevops/behat-format-progress-fail
composer require --dev behat/behat:^3.33 --with-all-dependencies
```

Order matters here. Releases after 1.5.1 declare a Composer conflict with Behat 3.33.0 and newer, but older releases don't. If your version constraint allows one of those older releases, Composer quietly falls back to it instead of reporting an error. Upgrade Behat first and you can end up on the new Behat with an old copy of this package still installed.

Until you finish step 2, your Behat config still references the extension, so Behat stops with "extension file or class could not be located".

Going straight to Behat 4? Use `^4.0` instead, and read Behat's [upgrading to 4.0](https://docs.behat.org/en/v4.x/releases/upgrading-to-4.0.html) guide first: 4.x drops YAML config and annotation-based step definitions.

### 2. Switch your config to the core formatter

| This extension | Behat core |
|---|---|
| `DrevOps\BehatFormatProgressFail\FormatExtension` under `extensions` | Remove it - the formatter is built in |
| `--format=progress_fail` | `--format=progress` with `inline_failures` turned on |
| `show_output` | `show_output` on the `progress` formatter, with the same 4 values: `yes`, `no`, `on-fail` and `in-summary` (the default in both) |
| `name`, `base_path` | Nothing to carry over - the core formatter is always `progress`, and it already prints paths relative to `%paths.base%` |

On Behat 3.x, that comes down to this in `behat.yml`:

>behat.yml
```yaml
default:
  formatters:
    progress:
      inline_failures: true
      show_output: in-summary
```

Behat 4 reads only PHP config, so there the same settings go in `behat.php`. This form works on 3.33.0 and newer too:

>behat.php
```php
<?php

use Behat\Config\Config;
use Behat\Config\Formatter\ProgressFormatter;
use Behat\Config\Formatter\ShowOutputOption;
use Behat\Config\Profile;

return (new Config())
    ->withProfile((new Profile('default'))
        ->withFormatter(new ProgressFormatter(inlineFailures: true, showOutput: ShowOutputOption::InSummary)));
```

`showOutput` takes `ShowOutputOption::Yes`, `ShowOutputOption::No`, `ShowOutputOption::OnFail` or `ShowOutputOption::InSummary`. If your config is still YAML, `vendor/bin/behat --convert-config` writes the `behat.php` for you. It deletes the `behat.yml` it converts, so commit first.

Or pass it on the command line:

```bash
vendor/bin/behat --format=progress --format-settings='{"inline_failures": true}'
```

### What looks different

The core output isn't byte-for-byte the same, which matters if anything in your CI greps the logs:

- The block header reads `--- FAILED ---` instead of `--- FAIL ---`.
- Pending and undefined steps get an inline block too (`--- PENDING ---`, `--- UNDEFINED ---`), where this extension printed a `P` or `U`.
- Step locations read `# features/apples.feature:11` instead of `# (features/apples.feature):11`.
- Exceptions read the way Behat's end-of-run summary prints them, so a plain exception gets its class appended to the message, like `(RuntimeException)`.

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
_This repository was created using the [Scaffold](https://getscaffold.dev/) project template_

