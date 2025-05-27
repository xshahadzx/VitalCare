<!-------------------------------------------------------------------------sign out button php : all----------------------------------------------------------------------- -->
<?php 
    include("../phpFiles/connect.php");
    $conn->close();
    session_start();
    session_destroy();
    session_abort();
    foreach ($_COOKIE as $name => $value) {
        setcookie($name, "", time() - 3600, "/");
    }
    header("Location: index.php");
?>