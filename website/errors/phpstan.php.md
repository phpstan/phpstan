---
title: "phpstan.php"
ignorable: true
---

## Code example

This error wraps native PHP errors (warnings, notices, deprecations) that occur during analysis.

## Why is it reported?

A PHP warning, notice, or deprecation was triggered while PHPStan was analysing the code. This happens when PHP itself encounters an issue during the analysis phase. The error message includes the PHP error level (e.g., `Warning`, `Notice`, `Deprecated`) followed by the error text.

## How to fix it

Fix the underlying PHP issue that causes the warning, notice, or deprecation. The error message from PHP itself describes what went wrong.
