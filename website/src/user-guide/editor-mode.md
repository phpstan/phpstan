---
title: Editor Mode
searchKeywords:
  - lsp
  - language server
  - ide
  - vscode
  - phpstorm
  - editor integration
---

<div class="text-xs inline-block border border-green-600 text-green-600 bg-green-100 rounded px-1 mb-4">Available in PHPStan 2.1.17</div>

<div class="text-xs inline-block border border-green-600 text-green-600 bg-green-100 rounded px-1 mb-4">Available in PHPStan 1.12.27</div>

PHPStan can be used to integrate its static analysis into editors, IDEs and other use cases like mutation testing tools.

The idea is that when the user or the tool edit a file, the file is stored in a temporary file path and is supposed to **replace a file from within the project**.

Let's say you're editing `src/Foo.php` in your project and you store the temporary version to analyse in `/tmp/9539itfeh2.php`. The way you run PHPStan is to run `analyse` command as usual with the configuration file, rule level, analysed paths, everything you're used to passing in when you're running PHPStan, but also add `--tmp-file` and `--instead-of` CLI options:

```bash
vendor/bin/phpstan analyse \
	-l 8 \
	-c build/phpstan.neon \
	--tmp-file /tmp/9539itfeh2.php \
	--instead-of src/Foo.php \
	app/ src/ tests/
```

PHPStan will analyse the whole project, but will act as if `src/Foo.php` was replaced by `/tmp/9539itfeh2.php`. The CLI options `--tmp-file` and `--instead-of` have several effects on the analysis.

Original file is not analysed in favour of the temporary file
----------------------

Analysis of file `src/Foo.php` will be skipped but file `/tmp/9539itfeh2.php` will be analysed instead. The `ignoreErrors` entries for the original file will still apply.


Result cache is properly restored
----------------------

The result cache will act as if the `src/Foo.php` file had the contents of `/tmp/9539itfeh2.php`. If it's up-to-date and you only changed something inside a function body, it's very likely only the edited file will be reanalysed.

If you change a public function signature or some other detail other files in the project might be interested in, the edited file and the files dependent on it will be reanalysed. It all seamlessly works.


Result cache is not saved
----------------------

Because you're analysing temporary changes that might not be saved in the project in the end, this "editor mode" analysis doesn't save the result cache. This also enables running multiple "editor mode" PHPStan analyses at the same time in parallel.
