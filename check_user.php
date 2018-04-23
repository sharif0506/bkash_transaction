<?php

function getAlepoConnection() {

    $host = "192.168.102.46";
    $username = "tanveer";
    $password = "t@n4EE8r";

    $dbName = "alepocrm";

    $connection = mysqli_connect($host, $username, $password, $dbName);

    if (!$connection) {
        die("Connection of alepocrm failed : " . mysqli_connect_error());
    }
    return $connection;
}

function concatQubeeAccountNo($accountNumber) {

    while (strlen($accountNumber) < 20) {
        if (strlen($accountNumber) < 15) {
            $accountNumber = "0" . $accountNumber;
        } else {
            $accountNumber = "QUBEE" . $accountNumber;
        }
    }

    return $accountNumber;
}

function checkUserExist($accountNumber) {

    $userExist = FALSE;

    $connection = getAlepoConnection();

    $sql = "SELECT * FROM users WHERE CUSTOMFIELD4 = '$accountNumber'";

    $result = mysqli_query($connection, $sql);

    if (mysqli_num_rows($result) != 0) {

        $userExist = TRUE;
    }
    mysqli_close($connection);

    return $userExist;
}

function getUserID($accountNo) {

    $userID = "";

    if (strlen($accountNo) < 6 || strlen($accountNo) > 7) {
        return $userID;
    }

    $accountNumber = concatQubeeAccountNo($accountNo);

    if (!checkUserExist($accountNumber)) {
        return $userID;
    }
    //if valid account number then
    else {
        $connection = getAlepoConnection();

        $sql = "SELECT USERID FROM users WHERE CUSTOMFIELD4 = '$accountNumber'";

        $result = mysqli_query($connection, $sql);

        while ($row = mysqli_fetch_assoc($result)) {

            $userID = $row['USERID'];
            mysqli_close($connection);
            return $userID;
        }
    }
}
