---
title: "Debugging PHPStan Performance: Identify Slow Files"
date: 2024-07-05
tags: guides
---

If you feel like PHPStan could be faster when analysing your project, you can debug what's actually making it slow. Usually the cause of slow analysis can be pinpointed to a single file or a handful of files in a project that make PHPStan's analysis crawl to a halt.

Before you start debugging, try enabling [Bleeding Edge](/blog/what-is-bleeding-edge). It makes analysis in huge codebases run much faster. It comes with a [slight backward compatibility break](/blog/phpstan-1-6-0-with-conditional-return-types#lower-memory-consumption) with the advantage of breaking bidirectional memory references, making the job of garbage collector easier.

If that didn't help, run PHPStan again but add `-vvv --debug` [command line options](/user-guide/command-line-usage). PHPStan will run in a single thread instead of in multiple processes at once, and it will print the elapsed time and consumed memory during the analysis of each file:

```
/var/www/src/Database/Eloquent/Relations/MorphToMany.php
--- consumed 6 MB, total 200 MB, took 0.11 s
/var/www/src/Database/Eloquent/Relations/HasOneThrough.php
--- consumed 0 B, total 200 MB, took 0.06 s
/var/www/src/Database/Eloquent/Relations/MorphPivot.php
--- consumed 0 B, total 200 MB, took 0.06 s
/var/www/src/Database/Eloquent/Relations/Pivot.php
--- consumed 2 MB, total 202 MB, took 0.09 s
/var/www/src/Database/Eloquent/Relations/HasOneOrMany.php
--- consumed 0 B, total 202 MB, took 0.15 s
/var/www/src/Database/Eloquent/Relations/BelongsTo.php
--- consumed 0 B, total 202 MB, took 0.15 s
```

You can either watch this log go by in realtime with your own eyes and spot files that took too much time or memory. Or you can redirect the output to a file:

```
vendor/bin/phpstan -vvv --debug > phpstan.log
```

And parse this file with a [simple script](https://gist.github.com/ruudk/41897eb59ff497b271fc9fa3c7d5fb27) by [Ruud Kamphuis](https://github.com/ruudk) that sorts the file list and puts the files that took the most time on top:

```php
<?php declare(strict_types=1);

$log = new SplFileObject("phpstan.log");

$logs = [];
$file = null;
while (!$log->eof()) {
    $line = trim($log->fgets());
    if ($line === '') {
        continue;
    }
    if ($file === null) {
        $file = $line;
        continue;
    }
    if (preg_match('/took (?<seconds>[\d.]+) s/', $line, $matches) !== 1) {
        continue;
    }

    $logs[] = [(float) $matches['seconds'], $file];
    $file = null;
}

usort($logs, fn(array $left, array $right) => $right[0] <=> $left[0]);
$logs = array_slice($logs, 0, 20);

echo "Slowest files" . PHP_EOL;
foreach ($logs as $log) {
    echo sprintf("%.2f seconds: %s", $log[0], $log[1]) . PHP_EOL;
}
```

Save this file into `parse.php` in the same directory where `phpstan.log` is and run it:

```bash
php parse.php
```

It will output 20 files that took the most time:

```
Slowest files
4.46 seconds: /var/www/src/Collections/LazyCollection.php
2.71 seconds: /var/www/src/Support/Str.php
2.56 seconds: /var/www/src/Console/Command.php
2.45 seconds: /var/www/src/Validation/Validator.php
2.44 seconds: /var/www/src/Database/Query/Builder.php
2.41 seconds: /var/www/src/Collections/Collection.php
2.12 seconds: /var/www/src/Database/Eloquent/Builder.php
2.10 seconds: /var/www/src/Foundation/Testing/TestCase.php
2.01 seconds: /var/www/src/Database/Eloquent/Model.php
...
```

What to do with this list? It's up to you to decide. Maybe the slowest file is really large and maybe even auto-generated. Analysing it with PHPStan doesn't probably bring too much value, so you can [exclude it from the analysis](/user-guide/ignoring-errors#excluding-whole-files) and make PHPStan immediately faster.

But if the slowest file isn't that large, you've just found a real bottleneck which is an opportunity to make PHPStan faster. Take this file and [open a new bug report](https://github.com/phpstan/phpstan/issues/new?template=Bug_report.yaml) in PHPStan's GitHub repository so we can take a look and use it to make PHPStan faster.

Happy debugging!

---

Do you like PHPStan and use it every day? [**Consider sponsoring** further development of PHPStan on GitHub Sponsors and also **subscribe to PHPStan Pro**](/sponsor)! I’d really appreciate it!

