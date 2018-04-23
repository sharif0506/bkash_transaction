<?php

function sendAlert() {

//    ini_set('sendmail_from', "sharifur.rahman@qubee.com.bd");
//    ini_set('SMTP', "192.168.103.24");
//    ini_set('smtp_port', 25);

    $to = 'sharifur.rahman@qubee.com.bd,moshiur.rahman@qubee.com.bd';
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
    echo 'Bkash server is connected succesfully.';
    fclose($sock);
}