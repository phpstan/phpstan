---
title: "constant.attributesRuleCannotRun"
ignorable: false
---

## Code example

This error cannot be demonstrated with a simple code example because it depends on the PHP runtime version used to run PHPStan.

## Why is it reported?

PHPStan needs PHP 8.5 or later as its runtime to check attributes on global constants. The analysed code uses attributes on a global constant (a feature available since PHP 8.5), but the PHP version running PHPStan itself is older than 8.5.

This is a limitation of the PHPStan runtime environment, not of the analysed code itself.

## How to fix it

Run PHPStan with PHP 8.5 or later to enable checking attributes on global constants.
