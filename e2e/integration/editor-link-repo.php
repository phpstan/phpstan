<?php

$gitUrl = trim(shell_exec('git remote get-url origin'));
$commit = trim(shell_exec('git rev-parse HEAD'));

$repoUrl = preg_replace('/\.git$/', '', $gitUrl);
$config = [];
$config['parameters']['editorUrlTitle'] = $repoUrl .'/commit/'. $commit. '/%%relFile%%#L%%line%%';

return $config;
