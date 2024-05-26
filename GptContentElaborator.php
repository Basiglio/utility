<?php

namespace classes;

class GptContentElaborator
{
    private $gptApiKey;
    private $gptApiUrl;
    private $model;
    private $maxTokens;
    private $temperature;
    private $logger;

    public function __construct($apiKey, $apiUrl, Logger $logger)
    {
        $this->gptApiKey = $apiKey;
        $this->gptApiUrl = $apiUrl;
        // Impostazioni predefinite del modello GPT
        $this->model = "gpt-3.5-turbo"; // Aggiornato il modello a uno di chat
        $this->maxTokens = 200; // Limite di token predefinito per articolo
        $this->temperature = 0.7; // Temperatura predefinita
        $this->logger = $logger;
    }

    // Metodo per impostare il modello GPT
    public function setModel($model)
    {
        $this->model = $model;
    }

    // Metodo per impostare il limite di token per articolo
    public function setMaxTokens($maxTokens)
    {
        $this->maxTokens = $maxTokens;
    }

    // Metodo per impostare la temperatura
    public function setTemperature($temperature)
    {
        $this->temperature = $temperature;
    }

    public function processContent($data): array
    {
        $processedData = [];

        // Loop attraverso i dati di input
        foreach ($data as $item) {

            foreach ($item as $value) {
                $title = $value['title'];
                $content = $value['content'];
                $term = $value['category'];


                //TODO:il titolo deve essere massimo di 100 caratteri

                // Elabora il contenuto con ChatGPT
                $processedTitle = $this->processWithChatGPT($title,'title');
                $processedContent = $this->processWithChatGPT($content,'content');

                // Aggiungi i dati elaborati all'array dei dati processati
                $processedContents = [
                    'title' => $processedTitle,
                    'content' => $processedContent,
                    'category' => $term
                ];

                $processedData[] = $processedContents;
            }
        }

        return $processedData;
    }


    private function processWithChatGPT($text,$type)
    {

        $requestData = match ($type) {

            "title" => [
                'model' => $this->model,
                'max_tokens' => 15,
                'temperature' => $this->temperature,
                'messages' => [
                    ['role' => 'user', 'content' => 'Sei un esperto redattore del giornare newspaper rielabora questo testo in chiave SEO massimo 30 caratteri' . $text],
                ]
            ],
            "content" => [
                'model' => $this->model,
                'max_tokens' => 375,
                'temperature' => $this->temperature,
                'messages' => [
                    ['role' => 'user', 'content' => 'Sei un esperto redattore del giornare newspaper rielabora questo testo in chiave SEO massimo 1500 caratteri, rimuovi eventuali riferimenti a siti esterni che potrebbero essere menzionati nel contenuto che leggi' . $text],
                ]
            ],
            default => [
                'model' => $this->model,
                'max_tokens' => $this->maxTokens,
                'temperature' => $this->temperature,
                'messages' => [
                    ['role' => 'user', 'content' => 'Rielabora questo testo in chiave SEO' . $text],
                ]
            ],
        };

        // Esegui la chiamata alle API di ChatGPT per la rielaborazione del contenuto
        $response = $this->callGptApi($requestData);

        //$this->logger->logMessage("response. " . var_export($response['choices'][0]['message']['content'],true));


        // Verifica se la chiamata API ha avuto successo
        if (isset($response['choices'][0]['message']['content'])) {

            //$this->logger->logMessage("Dati creati correttamente con ChatGPT. Dettagli: " . json_encode($response));

            // Restituisci i dati elaborati
            return $response['choices'][0]['message']['content'];

        } else {

            // In caso di errore nella chiamata API, registra un messaggio di errore
            $this->logger->logMessage("Errore: impossibile elaborare i dati con ChatGPT. Dettagli: " . json_encode($response));
            return '';
        }
    }


    private function callGptApi($requestData): array
    {
        // Configura e invia la richiesta cURL alle API di ChatGPT
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->gptApiUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->gptApiKey
        ]);

        $responseJson = curl_exec($ch);

        // Gestisci eventuali errori nella chiamata API
        if (curl_errno($ch)) {
            // Registra un messaggio di errore nel logger
            $this->logger->logMessage('Errore nella chiamata API di ChatGPT: ' . curl_error($ch));
            // In caso di errore, restituisci un array vuoto
            return [];
        }

        // Decodifica la risposta JSON e verifica eventuali errori
        $response = json_decode($responseJson, true);
        if (isset($response['error'])) {
            // Registra un messaggio di errore nel logger
            $this->logger->logMessage("Errore nella risposta API di ChatGPT: " . json_encode($response['error']));
            // In caso di errore, restituisci un array vuoto
            return [];
        }

        // Restituisci i dati elaborati
        return $response;
    }
}

