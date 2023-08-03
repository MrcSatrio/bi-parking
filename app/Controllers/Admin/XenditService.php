<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;


class XenditService extends BaseController
{
    private $api_key = 'xnd_development_0u4Yv65nvJdjycPu1G6VS9lZxXFBnIVMaPYwIgmhQhtSpWHlotJv8x3tR4Ipg';

    public function createInvoice($params)
    {
        $api_url = 'https://api.xendit.co/v2/invoices';

        // Encode API Key ke Base64
        $base64_encoded = base64_encode($this->api_key . ":");

        // Sertakan dalam header Authorization
        $auth_header = "Authorization: Basic " . $base64_encoded;

        // Persiapkan data JSON untuk dikirim sebagai payload
        $json_payload = json_encode($params);

        // Konfigurasi cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', $auth_header));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_payload);

        // Eksekusi cURL dan dapatkan respons
        $response = curl_exec($ch);
        curl_close($ch);

        // Parsing respons sebagai array
        return json_decode($response, true);
    }
}

$params = [
    'external_id' => 'demo_147580196270',
    'payer_email' => 'sample_email@xendit.co',
    'description' => 'Trip to Bali',
    'amount' => 32000,
    'for-user-id' => '5c2323c67d6d305ac433ba20'
];

$xenditService = new \App\Controllers\Admin\XenditService();
$xendit_response = $xenditService->createInvoice($params);

var_dump($xendit_response);