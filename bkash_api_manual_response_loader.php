<?php

require_once 'DBConnection.php';

function sendAlert() {

    $to = 'it.billing@qubee.com.bd,ccc.finance@qubee.com.bd';
    $subject = 'bKash api alert';
    $message = 'Dear concern,' . PHP_EOL . '' . PHP_EOL .
            'bKash server is not reachable.Failed to pull transactions data.'
            . PHP_EOL . '' . PHP_EOL . 'QUBEE';

    if (mail($to, $subject, $message)) {
        echo 'Alert mail is sent successfully.' . PHP_EOL;
    } else {
        echo 'Failed to send alert mail.' . PHP_EOL;
    }
}

$dbObject = new DBConnection();
$dbConnection = $dbObject->getConnection();

if ($dbConnection->connect_error) {
    die("Connection failed: " . $dbConnection->connect_error);
}

$date = new DateTime("now", new DateTimeZone('Asia/Dhaka'));

$endTime = $date->format('Y-m-d H:i:s'); //current time

$startTime = '2018-01-22 11:15:00';
$finishTime = '2018-01-22 12:15:00';
$path = "https://www.bkashcluster.com:9081/dreamwave/merchant/trxcheck/periodicpullmsg?user=AUGEREWIRELESS&pass=delta2victor2sierra&msisdn=01841178233&start_datetime=$startTime&end_datetime=$finishTime";

$ip = "www.bkashcluster.com"; //IP or web addy
$port = "9081"; //Port

$sock = @fsockopen($ip, $port, $num, $error, 2); //2 is the ping time, you can sub what you need
//Since fsockopen function returns TRUE or FALSE 

if (!$sock) {
    echo( "Bkash site appears to be closed" . PHP_EOL );
    sendAlert();
    die("Alert: BKash site is closed. Failed to load transactions." . PHP_EOL);
}

if ($sock) {
    echo 'Bkash server is succesfully connected .' . PHP_EOL;
    fclose($sock);
}

$xml = simplexml_load_file($path);

$json = json_encode($xml);

$array = json_decode($json, true);

$transactions = $array['transaction'];

$flag = 0;

foreach ($transactions as $transaction) {

    if (!is_array($transaction)) {
        $flag = 1;
        break;
    }

    $transactionStatus = $transaction['trxStatus'];

    if ($transactionStatus === "0000") {

        $trxId = $transaction['trxId'];

        $searchQuery = "SELECT * FROM transactions WHERE trxId = '$trxId'";

        $searchResult = $dbConnection->query($searchQuery);

        if ($searchResult->num_rows == 0) {

            $trxStatus = $transactionStatus;

            if (array_key_exists('reversed', $transaction)) {
                $reversed = $transaction['reversed'];
            } else {
                $reversed = 0;
            }
            $service = $transaction['service'];
            $sender = $transaction['sender'];
            $receiver = $transaction['receiver'];
            $currency = $transaction['currency'];
            $amount = $transaction['amount'];
            $reference = $transaction['reference'];
            $counter = $transaction['counter'];
            $trxTimestamp = $transaction['trxTimestamp'];
            $status = 0;

            $sql = "INSERT INTO transactions VALUES('','$trxId', '$trxStatus', '$reversed', '$service', '$sender',"
                    . " '$receiver', '$currency', '$amount', '$reference', '$counter', '$trxTimestamp', '$status','$endTime' )";

            if ($trxStatus === '0000' && $amount > 0) {
                if ($dbConnection->query($sql) === TRUE) {
                    echo "<h3>Result</h3>";
                    echo "Successfully stored data: $trxId, $trxStatus, $sender, $receiver, $reference, $trxTimestamp";
                } else {
                    echo "Error occured: " . $sql . "<br>" . $dbConnection->error;
                }
            }
        } else {
            echo "<h3>Result</h3>";
            echo "Already exists the data which trxId is: $trxId" . PHP_EOL;
        }
    } else {
        echo "<h3>Result</h3>";
        echo 'error occuered' . PHP_EOL;
        echo 'Error code:' . $transactionStatus;
    }
}

if ($flag == 1) {
    $transactionStatus = $transactions['trxStatus'];

    if ($transactionStatus === "0000") {

        $trxId = $transactions['trxId'];

        $searchQuery = "SELECT * FROM transactions WHERE trxId = '$trxId'";

        $searchResult = $dbConnection->query($searchQuery);

        if ($searchResult->num_rows == 0) {

            $trxStatus = $transactionStatus;

            if (array_key_exists('reversed', $transactions)) {
                $reversed = $transactions['reversed'];
            } else {
                $reversed = 0;
            }
            $service = $transactions['service'];
            $sender = $transactions['sender'];
            $receiver = $transactions['receiver'];
            $currency = $transactions['currency'];
            $amount = $transactions['amount'];
            $reference = $transactions['reference'];
            $counter = $transactions['counter'];
            $trxTimestamp = $transactions['trxTimestamp'];
            $status = 0;

            $sql = "INSERT INTO transactions VALUES('','$trxId', '$trxStatus', '$reversed', '$service', '$sender',"
                    . " '$receiver', '$currency', '$amount', '$reference', '$counter', '$trxTimestamp', '$status','$endTime' )";

            if ($dbConnection->query($sql) === TRUE) {
                echo "<h3>Result</h3>";
                echo "Successfully stored data: $trxId, $trxStatus, $sender, $receiver, $reference, $trxTimestamp" . PHP_EOL;
            } else {
                echo "Error occured: " . $sql . "<br>" . $dbConnection->error;
            }
        } else {
            echo "<h3>Result</h3>";
            echo "Already exists the data which trxId is: $trxId" . PHP_EOL;
        }
    } else {
        echo "<h3>Result</h3>";
        echo 'error occuered' . PHP_EOL;
        echo 'Error code:' . $transactionStatus;
    }
}

echo PHP_EOL . '<br>Manual Data loading script execution is completed at: ' . $endTime . '.' . PHP_EOL;
echo PHP_EOL . '===============================================================================' . PHP_EOL;
$dbConnection->close();
