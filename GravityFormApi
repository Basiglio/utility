/**
 * classe che effettua una chiamata all'endpoint di gravity e restituisce il json di un determinato form
 */
class GravityFormApi {

    private $api_url;
    private $consumer_key;
    private $consumer_secret;
    private $form_id;

    public function __construct($api_url, $consumer_key, $consumer_secret, $form_id) {
        $this->api_url = $api_url;
        $this->consumer_key = $consumer_key;
        $this->consumer_secret = $consumer_secret;
        $this->form_id = $form_id;
    }

    public static function getFormEntries($api_url, $consumer_key, $consumer_secret, $form_id) {

        $instance = new self($api_url, $consumer_key, $consumer_secret, $form_id);

        $entries_endpoint = $instance->api_url . "/forms/{$instance->form_id}/entries";

        // Imposta le credenziali per l'autenticazione di base
        $auth = base64_encode("{$instance->consumer_key}:{$instance->consumer_secret}");

        // Inizializza cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $entries_endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Basic $auth"
        ]);

        // Esegui la richiesta cURL
        $response = curl_exec($ch);

        // Verifica errori cURL
        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            curl_close($ch);
            throw new Exception('Errore nel recupero dei dati: ' . $error_msg);
        }

        // Chiudi la sessione cURL
        curl_close($ch);

        // Decodifica la risposta JSON
        $entries = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Errore nella decodifica della risposta JSON: ' . json_last_error_msg());
        }

        return $entries;
    }
}
