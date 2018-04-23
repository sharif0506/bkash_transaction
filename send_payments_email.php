<?php
//require '/var/www/html/bkash_payment/PHPMailer/class.phpmailer.php';
//require '/var/www/html/bkash_payment/DBConnection.php';
require 'PHPMailer/class.phpmailer.php';
require 'DBConnection.php';

error_reporting(E_ERROR);

$dt = new DateTime("now", new DateTimeZone('Asia/Dhaka'));

$today = $dt->format('Y-m-d H:m:s');

$year = $dt->format('Y');
$month = $dt->format('m');
$day = $dt->format('d');
$hour = $dt->format('H');
$minute = $dt->format('i');
$second = $dt->format('s');

$dt->modify('-1 day');

$yesterday = $dt->format('Y-m-d');

$nameFormat = "$year" . "$month" . "$day" . "$hour" . "$minute" . "$second" . ".csv";

$transactionData = fopen($nameFormat, "w") or die("can't open the csv file");
//$transactionData = fopen("trxData/$nameFormat.csv", "w") or die("can't open the csv file");
fputcsv($transactionData, array('Transaction Id', 'Transaction Status', 'Reversed', 'Service', 'Sender', 'Receiver', 'Currency', 'Amount', 'Reference', 'Counter', 'Transaction Date', 'Status'));
$dbConnection = new DBConnection();

$connection = $dbConnection->getConnection();

// Check connection
if ($connection->connect_error) {
    die("Connection failed: " . $connonnection->connect_error);
}

$sql = "SELECT * FROM transactions WHERE trxTimestamp BETWEEN ('$yesterday 00:00:00') AND ('$yesterday 23:59:59')";

//$sql = "SELECT * FROM transactions";

$queryResult = $connection->query($sql);
if ($queryResult->num_rows > 0) {

    while ($transactions = $queryResult->fetch_assoc()) {

        $trxId = $transactions['trxId'];
        $trxStatus = $transactions['trxStatus'];
        $reversed = $transactions['reversed'];
        $service = $transactions['service'];
        $sender = $transactions['sender'];
        $receiver = $transactions['receiver'];
        $currency = $transactions['currency'];
        $amount = $transactions['amount'];
        $counter = $transactions['counter'];
        $trxTimestamp = $transactions['trxTimestamp'];
        $reference = $transactions['reference'];
        $status = $transactions['status'];

        fputcsv($transactionData, array($trxId, $trxStatus, $reversed, $service, $sender, $receiver, $currency, $amount, $reference, $counter, $trxTimestamp, $status));
    }
}

fclose($transactionData);

//ini_set('SMTP', "192.168.103.24");
//ini_set('smtp_port', 25);
//$to = 'sharifur.rahman@qubee.com.bd';
//$subject = 'BKash Payment Data';

$message = 'Dear concern,' . PHP_EOL . '' . PHP_EOL . 'Please find the payment data from bKash. '
        . PHP_EOL . '' . PHP_EOL .
//       'Status = 0 => user checking and payment not done'
//      . PHP_EOL .
//       'Status = 1 => user valid but payment not done'
//        . PHP_EOL . '' . PHP_EOL .
        'Status = -1 => user not found'
        . PHP_EOL .
        'Status = 2 => Payment posted successfully'
        . PHP_EOL . '' . PHP_EOL .
        'QUBEE IT';

$email = new PHPMailer();
$email->From = 'it@qubee.com.bd';
$email->FromName = 'QUBEE IT';
$email->Subject = 'BKash Payment Data';
$email->Body = $message;
$email->AddAddress('it.billing@qubee.com.bd');
$email->AddAddress('ccc.finance@qubee.com.bd');
$email->AddAddress('ccd.mgt@qubee.com.bd');
$email->AddAddress('bo.operations@qubee.com.bd');
$email->AddAddress('supervisor.callcenter@qubee.com.bd');

//$file_to_attach = "trxData/$nameFormat.csv";
$file_to_attach =$nameFormat;
$email->AddAttachment($file_to_attach, $nameFormat);

echo '<h3>Mail Status</h3>';

if ($email->Send()) {
    echo PHP_EOL . 'Mail send successfully at: ' . $today . PHP_EOL;
} else {
    echo PHP_EOL . 'Failed to send mail at: ' . $today . PHP_EOL;
}
echo '==========================================================='.PHP_EOL;