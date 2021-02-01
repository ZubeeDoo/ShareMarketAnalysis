<?php

include "readCsv.php";


$target_dir = "StockPriceCSV/";
$actualFileName = $_FILES["file"]["name"];
$FileName = date("Y_m_d",time());
$target_file = $target_dir . $FileName . ".csv";
$uploadOk = 1;

$MsgToDisplay ="";

$FileType = strtolower(pathinfo( basename($actualFileName) ,PATHINFO_EXTENSION));



// Check if file already exists
// if (file_exists($target_file)) {
//   echo "Sorry, file already exists.";
//   $uploadOk = 0;
// }

//Removing file
if(file_exists($target_file)) {
  chmod($target_file,0755); //Change the file permissions if allowed
  unlink($target_file); //remove the file
}

// Check file size
if ($_FILES["file"]["size"] > 50000000) {
  $MsgToDisplay = "Sorry, your file is too large.";
  $uploadOk = 0;
}

// Allow certain file formats
if($FileType != "csv") {
  $MsgToDisplay = "Sorry, only csv files are allowed. you are trying to upload a " . $FileType;
  $uploadOk = 0;
}

// Check if $uploadOk is set to 0 by an error
if ($uploadOk == 0) {
  echo json_encode(array("Msg" => $MsgToDisplay, "Status" => 500));
} else {
  if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
    $objReadData = ReadCsv($target_file);
    echo json_encode(array("FileName" => $FileName
                          , "MinDate" => $objReadData["MinDate"]
                          , "MaxDate" => $objReadData["MaxDate"]
                          , "AvailableCompany" => $objReadData["AvailableCompany"]
                          , "Msg" => "The file ". htmlspecialchars($actualFileName). " has been uploaded."
                          , "Status" => 200)) ;
  } else {
    echo json_encode(array("Msg" => "Sorry, there was an error uploading your file.", "Status" => 500));
  }
}
?>