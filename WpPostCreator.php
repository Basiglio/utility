<?php

namespace classes;

class WpPostCreator
{
    private $logger;
    private $apiKey;
    private $dalleEndpoint;

    public function __construct($logger, $apiKey, $dalleEndpoint) {

        $this->logger = $logger;
        $this->apiKey = $apiKey;
        $this->dalleEndpoint = $dalleEndpoint;

    }


    public function createPosts(array $posts): void
    {
        foreach ($posts as $post) {

            $this->createPost($post);
        }

    }

    private function createPost(array $item) {
        // Converte la data nel formato corretto
        $date = date('Y-m-d H:i:s');
        // Prepara i dati del post
        $post_data = array(
            'post_title'    => wp_strip_all_tags($item['title']),
            'post_content'  => $item['content'],
            'post_status'   => 'publish',
            'post_author'   => 1, // ID dell'autore
            'post_category' => array($this->getCategoryId($item['category'])), // Array degli ID delle categorie
            'post_date'     => $date,
        );

        // Inserisci il post nel database
        $post_id = wp_insert_post($post_data);

        if (is_wp_error($post_id)) {
            // Gestione degli errori
            $this->logger->logMessage('Errore nella creazione del post: ' . $post_id->get_error_message());

        } else if ($post_id == 0) {
            // Post non creato
            $this->logger->logMessage('Errore sconosciuto nella creazione del post.' . $post_id->get_error_message());

        } else {
            // Post creato con successo
            $this->logger->logMessage('Post creato con successo, ID: ' . $post_id);


            $this->logger->logMessage('Inizio generazione immagine con DALLE per il post ID: ' . $post_id);
            // Genera l'immagine con DALL-E
            $dalle_generator = new DalleImageGenerator($this->apiKey,$this->logger,$this->dalleEndpoint);
            $prompt = wp_strip_all_tags($item['title']);
            $response = $dalle_generator->generateImage($prompt);


            if (!empty($response['data'])) {
                $image_url = $response['data'][0]['url'];
                $upload_dir = wp_upload_dir();
                $image_filename = $upload_dir['path'] . '/immagine_' . $post_id . '.png';

                // Salva l'immagine nella directory di upload di WordPress
                $dalle_generator->saveImage($image_url, $image_filename);

                // Aggiungi l'immagine alla libreria media di WordPress e ottieni l'ID dell'allegato
                $attachment_id = $this->addImageToMediaLibrary($image_filename, $post_id);

                if (!is_wp_error($attachment_id)) {

                    // Imposta l'immagine come immagine in evidenza del post
                    $set_post_thumbnail = set_post_thumbnail($post_id, $attachment_id);
                    $this->logger->logMessage('Risultato caricamento immagine in evidenza, post ID: ' . $post_id . " risultato ".$set_post_thumbnail);

                } else {

                    $this->logger->logMessage('Errore nell\'aggiunta dell\'immagine alla libreria media: ' . $attachment_id->get_error_message());

                }
            } else {

                $this->logger->logMessage('Errore nella generazione dell\'immagine con DALL-E. ');

            }
        }
    }

    private function addImageToMediaLibrary($image_path, $post_id) {

        $filetype = wp_check_filetype(basename($image_path), null);
        $upload_dir = wp_upload_dir();

        $attachment = array(
            'guid'           => $upload_dir['url'] . '/' . basename($image_path),
            'post_mime_type' => $filetype['type'],
            'post_title'     => sanitize_file_name(basename($image_path)),
            'post_content'   => '',
            'post_status'    => 'inherit'
        );

        // Inserisci l'immagine nella libreria media
        $attach_id = wp_insert_attachment($attachment, $image_path, $post_id);

        // Genera i metadati dell'immagine
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attach_data = wp_generate_attachment_metadata($attach_id, $image_path);
        wp_update_attachment_metadata($attach_id, $attach_data);

        return $attach_id;
    }

    private function getCategoryId(string $categoryName): int
    {

        $category = get_category_by_slug(sanitize_title($categoryName));

        if ($category) {

            return $category->term_id;

        } else {

            $category = wp_insert_term($categoryName, 'category');

            if (is_wp_error($category)) {

                echo 'Errore nella creazione della categoria: ' . $category->get_error_message();

                return 0;

            } else {

                return $category['term_id'];

            }

        }

    }

}
