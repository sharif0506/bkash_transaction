<?php

require_once 'lib/nusoap.php';
require_once 'DBConnection.php';
require_once 'check_payment.php';
require_once 'check_user.php';

error_reporting(E_ERROR);
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
    die("Connection failed: " . $connection->connect_error);
}

$sql = "SELECT * FROM transactions WHERE status = 1;"; // select all valid data
$queryResult = $connection->query($sql);

if ($queryResult->num_rows > 0) {

    while ($transactions = $queryResult->fetch_assoc()) {

        $id = $transactions['id'];
//        $qubeeAccountNumber = $transactions['reference'];
//        $userID = $transactions['reference'];
        $userID = getUserID($transactions['reference']);
        $amount = $transactions['amount'];
        $paymentMethod = '0';
        $transactionType = 'credit';
        $paymentAgainst = 'Monthly Cost';
        $salesChannel = 'Dhaka';
        $paymentChannel = 'bkash';
        $description = $transactions['trxId'];


        $sender = $transactions['sender'];
        $receiver = $transactions['receiver'];

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
            'key' => 'userId',
            'value' => $userID
        ));

        array_push(
                $param['keyParam'], array(
            'key' => 'amount',
            'value' => $amount
        ));

        array_push(
                $param['keyParam'], array(
            'key' => 'TransactionType',
            'value' => $transactionType
        ));

        array_push(
                $param['keyParam'], array(
            'key' => 'paymentMethod',
            'value' => $paymentMethod
        ));

        array_push(
                $param['keyParam'], array(
            'key' => 'paymentAgainst',
            'value' => $paymentAgainst
        ));

        array_push(
                $param['keyParam'], array(
            'key' => 'salesChannel',
            'value' => $salesChannel
        ));

        array_push(
                $param['keyParam'], array(
            'key' => 'paymentChannel',
            'value' => $paymentChannel
        ));

        array_push(
                $param['keyParam'], array(
            'key' => 'Description',
            'value' => $description
        ));

        $alreadyPosted = checkPaymentExist($description);

        if ($alreadyPosted == FALSE) {

            $result = $client->call('ASE_BusinessService', array('request' => $param, 'operation' => 'postAmount'));

            if ($client->fault) {
                echo '<h2>Fault</h2><pre>';
                print_r($result);
                echo '</pre>';
            } else {

                $err = $client->getError();

                if ($err) {
                    echo '<h2>Error</h2><pre>' . $err . '</pre>';
                } else {

                    echo '<h2>Result</h2>';
                    $temp = $result['errorCode'];

                    if ($temp == 0) {

                        $updateQuery = "UPDATE transactions SET status = 2 WHERE id = $id";

                        if ($connection->query($updateQuery)) {
                            echo "Post payment is done successfully of: $description, $sender, $receiver,$reference, $counter, $trxTimestamp  ." . PHP_EOL;
                        } else {
                            echo "1.Error updating record: $description, $sender, $receiver,$reference, $counter, $trxTimestamp. Error:" . $connection->error;
                        }
                    } else {
                        echo 'Error occured in postAmount webservice. Error:';
                        echo $temp . "" . PHP_EOL;
                    }
                }
            }
        } else {

            $updateQuery = "UPDATE transactions SET status = 2 WHERE id = $id";

            echo '<h2>Result</h2>';
            if ($connection->query($updateQuery)) {
                echo "Post payment is done successfully of : $description, $sender, $receiver,$reference, $counter, $trxTimestamp  ." . PHP_EOL;
            } else {
                echo "Error updating record:  $description, $sender, $receiver,$reference, $counter, $trxTimestamp. Error:" . $connection->error;
            }
        }
    }//for each db rows
} else {
    echo '<h3>Result</h3>';
    echo 'No new user found.' . PHP_EOL;
}

$connection->close();

$date = new DateTime("now", new DateTimeZone('Asia/Dhaka'));
$endTime = $date->format('Y-m-d H:i:s'); //current time

echo PHP_EOL . '<br>Data payment script execution is completed at: ' . $endTime . '.' . PHP_EOL;
echo PHP_EOL . '===============================================================================' . PHP_EOL;
