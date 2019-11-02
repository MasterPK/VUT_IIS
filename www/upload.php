<?php


 
//The resource that we want to download.
$fileUrl = 'https://codeload.github.com/MasterPK/VUT_IIS/zip/master';
 
//The path & filename to save to.
$saveTo = './github_tmp/master.zip';
 
//Open file handler.
$fp = fopen($saveTo, 'w+');
 
//If $fp is FALSE, something went wrong.
if($fp === false){
    throw new Exception('Could not open: ' . $saveTo);
}
 
//Create a cURL handle.
$ch = curl_init($fileUrl);
 
//Pass our file handle to cURL.
curl_setopt($ch, CURLOPT_FILE, $fp);
 
//Timeout if the file doesn't download after 20 seconds.
curl_setopt($ch, CURLOPT_TIMEOUT, 20);
 
//Execute the request.
curl_exec($ch);
 
//If there was an error, throw an Exception
if(curl_errno($ch)){
    throw new Exception(curl_error($ch));
}
 
//Get the HTTP status code.
$statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
 
//Close the cURL handler.
curl_close($ch);
 
//Close the file handler.
fclose($fp);
 
if($statusCode == 200){
    echo "Downloaded!\n";
} else{
    echo "Status Code: " . $statusCode."\n";
    exit(1);
}

$zip = new ZipArchive;
$res = $zip->open('./github_tmp/master.zip');
if ($res === TRUE) {
  $zip->extractTo('./github_tmp/');
  $zip->close();
  unlink('./github_tmp/master.zip');
  echo "Unziped!\n";
} else {
  echo "Unzip error!\n";
  exit(1);
}
rrmdir("../app");

$source = "./github_tmp/VUT_IIS-master/";
$dest= "../../web/";

foreach (
 $iterator = new \RecursiveIteratorIterator(
  new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
  \RecursiveIteratorIterator::SELF_FIRST) as $item
) {
  if ($item->isDir()) {
    mkdir($dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
  } else {
    copy($item, $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
  }
}

rrmdir("./github_tmp/VUT_IIS-master");

function rrmdir($dir) {
  if (is_dir($dir)) {
    $objects = scandir($dir);
    foreach ($objects as $object) {
      if ($object != "." && $object != "..") {
        if (filetype($dir."/".$object) == "dir") rrmdir($dir."/".$object); else unlink($dir."/".$object);
      }
    }
    reset($objects);
    rmdir($dir);
  }
}