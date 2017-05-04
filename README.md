# behat-format-progress-fail
Behat output formatter to show progress as TAP and fails inline.

## For using
>behat.yml
```yaml
default:
  formatters:
    progress_fail: ~
  extensions:
    IntegratedExperts\BehatFormatProgressFail\FormatExtension: ~
```
>Mac OS/Linux terminal
```bash
vendor/bin/behat --format=progress_fail
```