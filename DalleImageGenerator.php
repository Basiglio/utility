<?php

namespace classes;

class DalleImageGenerator {

    private $api_key;
    private $api_url;
    private $logger;

    public function __construct($api_key, $logger, $endpoint_url) {

        $this->api_key = $api_key;
        $this->api_url = $endpoint_url;
        $this->logger = $logger;

    }

    public function generateImage($prompt, $num_images = 1, $size = '1024x1024') {

        $data = [
            'prompt' => $prompt,
            'n' => $num_images,
            'size' => $size
        ];

        list($ch, $response) = $this->makeRequest($data);

        //$this->logger->logMessage(var_export($response,true));


        if ($response === false) {
            die('Curl error: ' . curl_error($ch));
        }

        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($http_code != 200) {
            die('Errore: ' . $http_code . ' ' . $response);
        }

        return json_decode($response, true);
    }

    private function makeRequest($data) {
        $ch = curl_init();
        $options = [
            CURLOPT_URL => $this->api_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->api_key
            ],
            CURLOPT_POSTFIELDS => json_encode($data)
        ];
        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        curl_close($ch);

        return [$ch, $response];
    }

    public function saveImage($image_url, $filename) {
        $img_response = file_get_contents($image_url);
        if ($img_response !== false) {
            file_put_contents($filename, $img_response);
            echo "Immagine salvata come $filename\n";

            if (file_exists($filename)) {
                echo "Il file $filename è stato creato correttamente.\n";
            } else {
                echo "Errore: Il file $filename non è stato creato.\n";
            }
        } else {
            echo "Errore nel recupero dell'immagine\n";
        }
    }
}
