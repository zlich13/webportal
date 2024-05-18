<?php

// Connect to the database
include('../../dbconnection.php');

if (isset($_POST['number'])) {
    $number = $_POST['number'];
    $code = rand(10000, 99999);
    $expires = (time() + (60 * 6));

    $stmt = $con->prepare("INSERT INTO phone_verify (code, expires, phone) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE code = ?, expires = ?");
    $stmt->bind_param("iisii", $code, $expires, $number, $code, $expires);
    if ($stmt->execute()) {
        $respo = array(
                    'status' => 'success',
                    'message' => 'Code Sent'
                );
        // Set your Semaphore API key
        $apiKey = 'insert_semaphore_api_key_here';
        $message = 'Your ACTS Web Portal phone number verification code is ' . $code . '. Please use it within 5 minutes.';

        // Create the cURL request
        $ch = curl_init();

        // Set the request URL
        $url = 'https://semaphore.co/api/v4/messages';

        // Set the POST data
        $postData = array(
            'apikey' => $apiKey,
            'number' => $number,
            'message' => $message
        );

        // Set cURL options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Execute the request
        $response = curl_exec($ch);

        // Check for errors
        if ($response === false) {
            $respo = array(
                'status' => 'error',
                'message' => curl_error($ch)
            );
        } else {
            $result = json_decode($response, true);
            if (array_key_exists('number', $result)) {
                 $respo = array(
                    'status' => 'error',
                    'message' => 'Number invalid. Code not sent.'
                );
            } else {
              $respo = array(
                    'status' => 'success',
                    'message' => 'Code Sent'
                );
            }
        }
        // Close cURL resource
        curl_close($ch);
    } else {
        $respo = array(
            'status' => 'error',
            'message' => 'Generating code failed'
        );
    }
} else {
    $respo = array(
        'status' => 'error',
        'message' => 'No number set'
    );
}

// Send the response as JSON
header('Content-Type: application/json');
echo json_encode($respo);

?>
