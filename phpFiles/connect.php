<?php
// set up all the variables needed to connect to the database
$db_server= "localhost";
$db_user="root";
$db_pass= "";
$db_name="vitalcare";
$conn="";
try{
    $conn=mysqli_connect($db_server,$db_user,$db_pass,database: $db_name);
}
catch(Exception $e){
    echo "there was an error connecting to the database";
}
?>
