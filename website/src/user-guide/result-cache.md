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
  * [Result cache meta extensions](/developing-extensions/result-cache-meta-extensions)
* Errors in the last run
* Dependency tree of project files. If file `A.php` was modified since the last run, `A.php` and all the files calling or otherwise referencing all the symbols in `A.php` are analysed again.

Clearing the result cache
---------------

To clear the current state of the result cache, for example if you're developing [custom extensions](/developing-extensions/extension-types) and the result cache is getting stale too often, you can run the `clear-result-cache` command. [Learn more »](/user-guide/command-line-usage#clearing-the-result-cache)

Result cache also gets disabled when running with [`--debug`](/user-guide/command-line-usage#--debug).


Debugging the result cache
---------------

If you run the `analyse` command with `-vv`, PHPStan will output details about the result cache like:

* "Result cache not used because the cache file does not exist."
* "Result cache not used because of debug mode."
* "Result cache was not saved because of internal errors."
* "Result cache is saved."
* etc.


Setup in Continuous Integration
----------------

Taking advantage of the result cache in your CI pipeline can make your build a lot faster.

Here's an example of cache setup in GitHub Actions. First, set `tmpDir` in your [configuration file](/config-reference) to be inside your workspace:

```yaml
parameters:
	tmpDir: tmp
```

### Setup in GitHub Actions

Because GitHub Actions do not overwrite existing cache entries with the same key, we need to make sure the cache always has a unique key. Also, we can save the cache even for failing builds with reported errors. Here's how the steps in a workflow could look like:

```yaml
  # checkout, setup-php, composer install...
  
  - name: "Restore result cache"
    uses: actions/cache/restore@v5
    with:
      path: tmp # same as in phpstan.neon
      key: "phpstan-result-cache-{% raw %}${{ github.run_id }}"{% endraw %}
      restore-keys: |
        phpstan-result-cache-

  - name: "Run PHPStan"
    run: "vendor/bin/phpstan"

  - name: "Save result cache"
    uses: actions/cache/save@v5
    if: {% raw %}${{ !cancelled() }}{% endraw %}
    with:
      path: tmp # same as in phpstan.neon
      key: "phpstan-result-cache-{% raw %}${{ github.run_id }}"{% endraw %}
```

Learn more: [Workflow syntax for GitHub Actions](https://docs.github.com/en/actions/using-workflows/workflow-syntax-for-github-actions), [actions/cache](https://github.com/actions/cache)

### Setup in GitLab CI

```yaml
phpstan:
  cache:
	  key: "phpstan-result-cache-$CI_COMMIT_REF_NAME"
	  fallback_keys:
	    - "phpstan-result-cache-$CI_DEFAULT_BRANCH"
    paths:
      - tmp # same as in phpstan.neon
    when: 'always'
```

Learn more: [Get started with GitLab CI/CD](https://docs.gitlab.com/ci/)

### Setup in Bitbucket Pipelines CI

```yaml
definitions:
  caches:
    phpstan:
      key:
        files: # the files you want pipelines to check for changes when deciding whether to use the cache, or download fresh dependencies.
          - composer.lock
      path: tmp # same as in phpstan.neon

pipelines:
  default:
    - step:
	  name: Static Analysis
	  script:
		- composer install
		- vendor/bin/phpstan
	  caches:
		- composer
		- phpstan
```

Learn more: [Get started with Bitbucket Pipelines](https://support.atlassian.com/bitbucket-cloud/docs/get-started-with-bitbucket-pipelines/), [Caches](https://support.atlassian.com/bitbucket-cloud/docs/cache-dependencies/#Custom-caches-for-other-build-tools-and-directories)
