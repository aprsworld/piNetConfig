<?php


function toggleFeature($feature){
  $oldDir = getcwd();
  chdir('/tmp/');
  switch($feature) {
    case 'test1':
      exec('sudo ./sampleScript.sh');
      break;
    case 'test2':
      exec('sudo ./sampleScript1.sh');
      break;
    default:
      echo 'no feature by that name';
  }
  chdir($oldDir);
}

toggleFeature($_GET['feature']);

php?>
