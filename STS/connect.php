<?php
   //Connect To Database
    $hostname='srv346.hstgr.io';
    $username='u183359785_ched2';
    $password='Syncopation@020';
    $dbname='u183359785_chedro2_db';

    $connection = mysqli_connect($hostname, $username, $password, $dbname);
    if (!$connection) {
        die('Could not connect: ' . mysqli_connect_error());
    }
?>