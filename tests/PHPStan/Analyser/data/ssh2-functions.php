<?php

/** @var resource $resource */
$resource = doFoo();
$ssh2SftpStat = ssh2_sftp_stat($resource, __FILE__);

die;
