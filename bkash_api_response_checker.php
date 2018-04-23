<?php

error_reporting(E_ERROR);
//require_once '/var/www/html/bkash_payment/lib/nusoap.php';
//require_once '/var/www/html/bkash_payment/DBConnection.php';
//require_once '/var/www/html/bkash_payment/check_user.php';

require_once 'lib/nusoap.php';
require_once 'DBConnection.php';
require_once 'check_user.php';

$date = new DateTime("now", new DateTimeZone('Asia/Dhaka'));
$endTime = $date->format('Y-m-d H:i:s'); //current time

//$client = new nusoap_client('http://192.168.102.51:9091/tunnel-web/axis/Portlet_ase_BusinessDomainService');
$client = new nusoap_client('http://192.168.103.121:8080/tunnel-web/axis/Portlet_ase_BusinessDomainService');

$err = $client->getError();
if ($err) {
    echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
}

$dbConnection = new DBConnection();

$connection = $dbConnection->getConnection();

// Check connection
if ($connection->connect_error) {
    die("Connection failed: " . $connonnection->connect_error);
}

$sql = "SELECT * FROM transactions WHERE status = 0;";

$queryResult = $connection->query($sql);

if ($queryResult->num_rows > 0) {

    $logFile = fopen("bkash_api_check_response_data_log.txt", 'a') or die("can't open the log file");
//    $unknownUserFile = fopen("postPaymentWithNoUser.csv", "w") or die("can't open the csv file");
//    fputcsv($unknownUserFile, array('Transaction Date', 'Transaction Id', 'Sender', 'Receiver', 'Reference', 'Amount', 'Counter', 'Comment'));

    $date = new DateTime("now", new DateTimeZone('Asia/Dhaka'));
    $systemDate = $date->format('Y-m-d H:i:s'); //current time

    while ($transactions = $queryResult->fetch_assoc()) {

        $id = $transactions['id'];
        $trxId = $transactions['trxId'];

        $userID = getUserID($transactions['reference']);
//        $userID = $transactions['reference'];

        $sender = $transactions['sender'];
        $receiver = $transactions['receiver'];
        $amount = $transactions['amount'];
        $counter = $transactions['counter'];
        $trxTimestamp = $transactions['trxTimestamp'];
        $reference = $transactions['reference'];

        $param = array(
            'keyParam' => array(),
            'subSystemId' => 'external',
            'subSystemPassword' => 'password'
        );

        array_push(
                $param['keyParam'], array(
            'key' => 'UserID',
            'value' => $userID
        ));

        $result = $client->call('ASE_BusinessService', array('request' => $param, 'operation' => 'FetchUser'));

        // Check for a fault
        if ($client->fault) {
            echo '<h2>Fault</h2><pre>';
            print_r($result);
            echo '</pre>';
        } else {

            $err = $client->getError();
            // Check for a error
            if ($err) {
                echo '<h2>Error</h2><pre>' . $err . '</pre>';
            } else {
                // Display the result
                echo '<h2>Result</h2>';
                $temp = $result['errorCode'];
                $errMsg = $result['errorMessage'];

                if ($temp == 0) {

                    $updateQuery = "UPDATE transactions SET status = 1 WHERE id = $id";
                    if (mysqli_query($connection, $updateQuery)) {
                        echo "Record: $trxId, $sender, $receiver, $reference, $amount, $counter,$trxTimestamp updated as valid." . PHP_EOL;
                    } else {
                        echo "Record:$trxId, $sender, $receiver, $reference, $amount, $counter,$trxTimestamp updating failed. Error: " . mysqli_error($connection);
                    }
                    $log = "[Date:$systemDate], $trxId, $sender, $receiver, $reference, $amount, $counter,$trxTimestamp,  $errMsg" . PHP_EOL;

                    fwrite($logFile, $log);
                } else {

                    $updateQuery = "UPDATE transactions SET status = -1 WHERE id = $id";

                    if (mysqli_query($connection, $updateQuery)) {
                        echo "Record: $trxId, $sender, $receiver, $reference, $amount, $counter,$trxTimestamp  updated as invalid." . PHP_EOL;
                    } else {
                        echo "Record:$trxId, $sender, $receiver, $reference, $amount, $counter,$trxTimestamp updating failed. Error: " . mysqli_error($connection);
                    }
                    $log = "[Date:$systemDate], $trxId, $sender, $receiver, $reference, $amount, $counter,$trxTimestamp, $errMsg" . PHP_EOL;

                    fwrite($logFile, $log);

//                    fputcsv($unknownUserFile, array($trxTimestamp, $trxId, $sender, $receiver, $reference, $amount, $counter, "user not found"));
                }
            }
        }
    }
//    fclose($unknownUserFile);
    fclose($logFile);
} else {
    echo '<h3>Result</h3>';
    echo 'No new user found.' . PHP_EOL;
}
$connection->close();

echo PHP_EOL . '<br>Data checking script execution is completed at: ' . $endTime . '.' . PHP_EOL;
echo PHP_EOL . '===============================================================================' . PHP_EOL;

