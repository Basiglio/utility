<?php

namespace classes;

class Logger
{
    protected string $log_directory;
    protected string $log_file;

    public function __construct(string $log_directory, string $log_file)
    {
        $this->log_directory = $log_directory;
        $this->log_file = $log_file;
    }

    public function logMessage(string $message): void
    {
        // Crea la directory dei log se non esiste
        if (!file_exists($this->log_directory)) {
            mkdir($this->log_directory, 0755, true);
        }

        // Percorso completo del file di log
        $log_path = $this->log_directory . '/' . $this->log_file;

        // Formatta il messaggio con data/ora
        $log_message = '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;

        // Scrivi il messaggio nel file di log
        file_put_contents($log_path, $log_message, FILE_APPEND | LOCK_EX);
    }
}
