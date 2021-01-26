---
title: Core Concepts
---

<div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4" role="alert">

Please note that this website section is under development as of June 2020. The plan is to write about development of custom extensions in much more detail. Thanks for understanding.

</div>

<div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4" role="alert">

Be aware that PHPStan configuration settings are being cached. You might need to clear this cache when working on an extension in order for your phpstan instance to acknowledge your changes.

This cache is stored in the directory path used for temporary files, available through the use of `sys_get_temp_dir()`.

This cache can be cleared the `clear-result-cache` command. [Learn more »](/user-guide/command-line-usage#clearing-the-result-cache)

Result cache also gets disabled when running with [`--debug`](/user-guide/command-line-usage#--debug).

</div>

Reflection
-----------------

Type System
-----------------

Abstract Syntax Tree
-----------------

Scope
-----------------

Dependency Injection
-----------------
