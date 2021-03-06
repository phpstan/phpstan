---
title: Result Cache
---

PHPStan caches the result of the analysis so the subsequent runs are much faster. You should always analyse the whole project - the list of paths passed to the [`analyse` command](/user-guide/command-line-usage) should be the same to take advantage of the result cache. If the list of paths differs from run to run, the cache is rebuilt from the ground up each time.

<div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-4" role="alert">

You might notice the result cache isn't sometimes saved and PHPStan runs full analysis even if nothing changed since the last run. If the analysis result contains some serious errors like parse errors, result cache cannot be used for the next run because the files dependency tree might be incomplete.

</div>

The result cache is saved at `%tmpDir%/resultCache.php`. [Learn more about `tmpDir` configuration »](/config-reference#caching)

Result cache contents
--------------

* The last time a full analysis of the project was performed. The full analysis is performed at least every 7 days.
* Analysis variables used to invalidate a stale cache. If any of these values change, full analysis is performed again.
  * PHPStan version
  * PHP version
  * Loaded PHP extensions
  * [Rule level](/user-guide/rule-levels)
  * [Configuration files](/config-reference) hashes
  * Analysed paths
  * `composer.lock` files hashes
  * [Stub files](/user-guide/stub-files) hashes
  * [Bootstrap files](/config-reference#bootstrap) hashes
  * [Autoload file](/user-guide/command-line-usage#--autoload-file%7C-a) hash
* Errors in the last run
* Dependency tree of project files. If file `A.php` was modified since the last run, `A.php` and all the files calling or otherwise referencing all the symbols in `A.php` are analysed again.

Clearing the result cache
---------------

To clear the current state of the result cache, for example if you're developing [custom extensions](/developing-extensions/extension-types) and the result cache is getting stale too often, you can run the `clear-result-cache` command. [Learn more »](/user-guide/command-line-usage#clearing-the-result-cache)

Result cache also gets disabled when running with [`--debug`](/user-guide/command-line-usage#--debug).
