<?php
$oldDir = getcwd();
chdir('/tmp/');
exec('sudo ./sampleScript.sh');
chdir($oldDir);
php?>
