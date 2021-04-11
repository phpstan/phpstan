# PHPStan autoload error example

This repository demonstrates a potential bug in PHPStan: custom autoloading
fails when an anonymous class definition has dependencies that need to be
autoloaded. `require_once` complains the file does not exist when it really
does, likely due to how PHPStan's `BetterReflection` intercepts stream read
calls.

## Example

The following works without errors:

    $ vendor/bin/phpstan analyse

    vendor/bin/phpstan analyse
    Note: Using configuration file /Volumes/Work/git/phpstan-autoload-error-example/phpstan.neon.
     5/5 [▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓] 100%
    
    
    
     [OK] No errors

This is because PHPStan encounters the dependencies of the test classes by
scanning `src/`. If we run PHPStan only on a single test that does not use
`use` statements, though:

    $ vendor/bin/phpstan analyse tests/ClassATest.php
    Note: Using configuration file /Volumes/Work/git/phpstan-autoload-error-example/phpstan.neon.
     1/1 [▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓] 100%
    
     ------ ---------------------------------------------------------------------------------------------------------------
      Line   ClassATest.php
     ------ ---------------------------------------------------------------------------------------------------------------
             Internal error: Failed opening required '/Volumes/Work/git/phpstan-autoload-error-example/src/ClassA.php'
             (include_path='.:/usr/local/Cellar/php/8.0.3/share/php/pear')
             Run PHPStan with --debug option and post the stack trace to:
             https://github.com/phpstan/phpstan/issues/new?template=Bug_report.md
     ------ ---------------------------------------------------------------------------------------------------------------
    
    
     [ERROR] Found 1 error

In contrast, when we run PHPStan against an identical file except for `use`
statements, importing the dependencies:

    $ vendor/bin/phpstan analyse tests/ClassATestWithUse.php
    Note: Using configuration file /Volumes/Work/git/phpstan-autoload-error-example/phpstan.neon.
     1/1 [▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓] 100%
    
    
    
     [OK] No errors


