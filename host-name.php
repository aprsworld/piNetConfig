<?php

$hostname = gethostname();
echo json_encode(array('hostName' => $hostname));
?>
