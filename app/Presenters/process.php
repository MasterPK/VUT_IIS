<?php
//Remove spaces in PHP tag
$destfol = "Files/IDS/20/";
$destfol = $destfol . basename( $_FILES['sentfile']['name']) ;
 
if(move_uploaded_file($_FILES['sentfile']['tmp_name'], $destfol))
{
 echo basename( $_FILES['sentfile']['name']). " file uploaded";
}
else 
{

 echo "Oops, There was some Problem! Please fix it.";

}
?>