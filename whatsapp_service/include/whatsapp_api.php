<?php
// Function to send an offer via WhatsApp API using a predefined template with header image
function send_offer_to_customer($phone_number, $customer_name, $custom_message) {
    $api_url = ''; 
    $access_token = ''; 

    // Build the template message with dynamic parameters, including the image in the header
    $message = [
        'messaging_product' => 'whatsapp',
        'to' => $phone_number,
        'type' => 'template',
        'template' => [
            'name' => 'utility_customer', 
            'language' => ['code' => 'en_US'],
            'components' => [
                [
                    'type' => 'body',
                    'parameters' => [
                        [
                            'type' => 'text',
                            'text' => $customer_name 
                        ],
                        [
                            'type' => 'text',
                            'text' => $custom_message 
                        ]
                    ]
                ]
            ]
        ]
    ];

    // Initialize cURL
    $ch = curl_init($api_url);

    // Set cURL options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $access_token,
        'Content-Type: application/json'
    ]);

    // Execute the request and get the response
    $response = curl_exec($ch);

    // Check for cURL errors
    if (curl_errno($ch)) {
        $error_message = 'Error sending message: ' . curl_error($ch);
        curl_close($ch);
        return ['error' => true, 'message' => $error_message];
    }

    // Close cURL session
    curl_close($ch);

    // Decode the response body
    $decoded_response = json_decode($response, true);

    // Check for WhatsApp API-specific errors
    if (isset($decoded_response['error'])) {
        return [
            'error' => true,
            'message' => 'WhatsApp API Error: ' . $decoded_response['error']['message']
        ];
    }

    return [
        'error' => false,
        'response' => $response
    ];
}
?>
