---
title: "phpstanPlayground.configParameter"
ignorable: false
---

## Code example

This identifier is used internally by the [PHPStan playground](https://phpstan.org/try) to inform users about errors that would be reported if a specific configuration parameter was enabled.

```php
<?php declare(strict_types = 1);

// Example: if checkUninitializedProperties were enabled,
// the playground would show a phpstanPlayground.configParameter error
// suggesting the user enable that parameter.
```

## Why is it reported?

When analysing code on the PHPStan playground, certain rules that are gated behind configuration parameters (like [`checkUninitializedProperties`](/config-reference#checkuninitializedproperties)) are not active by default. The playground wraps these rules and reports their findings under the `phpstanPlayground.configParameter` identifier, with a tip explaining which configuration parameter the user should enable to get the error in their own project.

## How to fix it

The tip attached to this error indicates which configuration parameter to enable. Add the suggested parameter to the project's PHPStan configuration file to get this error reported as a regular error with its own specific identifier:

```yaml
parameters:
    checkUninitializedProperties: true
```

This identifier is specific to the PHPStan playground and will not appear in local analysis.
