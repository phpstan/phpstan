<?php

putenv('COLUMNS=500');

$commit = trim(shell_exec('git rev-parse HEAD'));

$gitUrl = trim(shell_exec('git remote get-url origin'));
$repoUrl = preg_replace('/\.git$/', '', $gitUrl);

$fileUrl = $repoUrl .'/blob/'. $commit. '/%%relFile%%#L%%line%%';

$config = [];
$config['parameters']['editorUrl'] = $fileUrl;
$config['parameters']['editorUrlTitle'] = $fileUrl;

return $config;
