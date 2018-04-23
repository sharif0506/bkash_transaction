<?php

function checkPaymentExist($trxID) {

    $paymentExist = FALSE;

//    $host = "192.168.102.51";
//    $username = "sharif";
//    $password = "ShAr!f01";
    $host = "192.168.102.31";
    $username = "tanveer";
    $password = "t@n4EE8r";

    $dbName = "alepocrm";

    $connection = mysqli_connect($host, $username, $password, $dbName);

    if (!$connection) {
        die("Connection of alepocrm failed : " . mysqli_connect_error());
    }

    $sql = "SELECT REMARK FROM payments WHERE REMARK = '$trxID'";
    $result = mysqli_query($connection, $sql);

    if (mysqli_num_rows($result) > 0) {

        $paymentExist = TRUE;
    }
    mysqli_close($connection);

    return $paymentExist;
}
