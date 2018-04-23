<?php

function getBaibossData($service_url, $requestData) {

    $postdata = json_encode($requestData);
    $httpHeader = array(
        'header' => 'Content-Type:application/json',
    );
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $service_url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeader);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

    $result = curl_exec($ch);
    curl_close($ch);
    return json_decode($result);
}

$queryCustomerApiPath = '192.168.226.102:7090/baicellsapi/customers/query';
$session_id = 'qubee1234';
$mac = '470110000001001';
$baiBossRequestData = array('session_id' => $session_id, 'imsi' => $mac);
$baiBossData = getBaibossData($queryCustomerApiPath, $baiBossRequestData);
print_r($baiBossData);
