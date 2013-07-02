<?php
    $con = mysql_connect("myserver","toxic_release","mypassword");
    if (!$con)
    {
        die('Could not connect to DB: ' . mysql_error());
    }

    mysql_select_db("toxic_release", $con);
?>
