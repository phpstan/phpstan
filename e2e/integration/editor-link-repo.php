<?php

$gitUrl = shell_exec('git remote get-url origin');
$commit = shell_exec('git rev-parse HEAD');

$repoUrl = rtrim(preg_replace('/\.git$/', '', $gitUrl));
$config = [];
$config['parameters']['editorUrl'] = sprintf('%s/commit/%s', $repoUrl, $commit);
$config['parameters']['editorUrlTitle'] = sprintf('%s/commit/%s', $repoUrl, $commit);

return $config;
