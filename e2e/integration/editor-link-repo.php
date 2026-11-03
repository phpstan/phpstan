<?php

putenv('COLUMNS=500');

$commit = trim(shell_exec('git rev-parse HEAD'));

$gitUrl = trim(shell_exec('git remote get-url origin'));
$repoUrl = preg_replace('/\.git$/', '', $gitUrl);

// %relFile% is relative to the working directory, which can be a subdirectory of the repository
$prefix = trim(shell_exec('git rev-parse --show-prefix'));

$fileUrl = $repoUrl .'/blob/'. $commit. '/' . $prefix . '%%relFile%%#L%%line%%';

$config = [];
$config['parameters']['editorUrl'] = $fileUrl;
$config['parameters']['editorUrlTitle'] = $fileUrl;

return $config;
