<?php declare(strict_types = 1);

// TYPO3 defines CRLF in SystemEnvironmentBuilder at runtime, but its
// Build/phpstan/phpstan-constants.php only defines LF and CR.
defined('CRLF') || define('CRLF', "\r\n");
