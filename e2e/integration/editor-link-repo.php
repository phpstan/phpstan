<?php

putenv('COLUMNS=300');

$gitUrl = shell_exec('git remote get-url origin');
$commit = shell_exec('git rev-parse HEAD');

$repoUrl = rtrim(preg_replace('/\.git$/', '', $gitUrl));
$config = [];
$config['parameters']['editorUrlTitle'] = sprintf('%s/commit/%s/%%relFile%%#L%%line%%', $repoUrl, $commit);

return $config;
