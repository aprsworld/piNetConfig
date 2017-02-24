<?php

define("SCRIPTDIR", "/opt/");

if(isset($_POST['feature'])){

  $postData = $_POST['feature'];
}

function toggleFeature($feature){
  $oldDir = getcwd();
  chdir(SCRIPTDIR);
  switch($feature) {
    case 'test1':
      exec('sudo ./sampleScript.sh');
      break;
    case 'test2':
      //todo: create result variable so that feed back can be returned to GUI
      exec('sudo ./sampleScript1.sh');
      break;
    default:
      echo 'no feature by that name';
  }
  chdir($oldDir);
  echo json_encode(array('feature' => $feature));
}

//if data was sent to the script, we run the function
if(isset($postData)){
  toggleFeature($postData);
}
else{ //otherwise, we check the data
   //TODO Write data checking function
}
php?>
