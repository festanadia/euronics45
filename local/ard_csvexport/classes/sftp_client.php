<?php
// local/ard_csvexport/classes/sftp_client.php

namespace local_ard_csvexport;

defined('MOODLE_INTERNAL') || die();

/**
 * SFTP client class for secure file transfers using phpseclib only.
 */
class sftp_client {

    /**
     * Upload file via SFTP using phpseclib.
     */
    public function upload_file($host, $port, $username, $password, $localfile, $remotefile) {
        
        mtrace("=== SFTP UPLOAD START (phpseclib autoloader) ===");
        mtrace("Target: $host:$port");
        mtrace("User: $username");
        mtrace("Local file: $localfile");
        mtrace("Remote file: $remotefile");
        
        // Verifica che il file locale esista
        if (!file_exists($localfile)) {
            mtrace("ERROR: Local file does not exist: $localfile");
            return false;
        }
        
        $filesize = filesize($localfile);
        mtrace("Local file size: $filesize bytes");
        
        // Setup autoloader di Composer
        if (!$this->setup_composer_autoloader()) {
            mtrace("ERROR: Cannot setup Composer autoloader");
            return false;
        }
        
        // Verifica che la classe SFTP sia disponibile
        if (!class_exists('phpseclib3\\Net\\SFTP')) {
            mtrace("ERROR: phpseclib3\\Net\\SFTP class not available");
            return false;
        }
        
        mtrace("phpseclib3\\Net\\SFTP class available");
        
        try {
            // Crea connessione SFTP
            $sftp = new \phpseclib3\Net\SFTP($host, $port);
            
            if (!$sftp->login($username, $password)) {
                mtrace("ERROR: SFTP authentication failed");
                return false;
            }
            
            mtrace("SFTP authentication successful");
            
            // Leggi il file locale
            $filecontents = file_get_contents($localfile);
            if ($filecontents === false) {
                mtrace("ERROR: Cannot read local file");
                return false;
            }
            
            mtrace("Local file read successfully");
            
            // Upload del file
            $result = $sftp->put($remotefile, $filecontents);
            
            if ($result) {
                mtrace("=== SFTP UPLOAD SUCCESS ===");
                return true;
            } else {
                mtrace("ERROR: SFTP upload failed");
                return false;
            }
            
        } catch (\Exception $e) {
            mtrace("ERROR: Exception during SFTP upload: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Setup dell'autoloader di Composer per phpseclib
     */
    private function setup_composer_autoloader() {
        
        mtrace("Setting up Composer autoloader...");
        
        $vendor_path = __DIR__ . '/../vendor';
        
        // Verifica se esiste l'autoloader di Composer
        $autoload_paths = [
            $vendor_path . '/autoload.php',                    // Composer standard
            $vendor_path . '/phpseclib/vendor/autoload.php',   // Se phpseclib ha il suo vendor
        ];
        
        foreach ($autoload_paths as $autoload_path) {
            if (file_exists($autoload_path)) {
                mtrace("Found Composer autoloader: $autoload_path");
                try {
                    require_once($autoload_path);
                    mtrace("Composer autoloader loaded successfully");
                    return true;
                } catch (\Exception $e) {
                    mtrace("Error loading Composer autoloader: " . $e->getMessage());
                }
            } else {
                mtrace("Autoloader not found: $autoload_path");
            }
        }
        
        // Se non trova l'autoloader di Composer, crea un autoloader personalizzato
        mtrace("Creating custom autoloader for phpseclib3...");
        return $this->setup_custom_autoloader();
    }
    
    /**
     * Setup di un autoloader personalizzato per phpseclib3
     */
    private function setup_custom_autoloader() {
        
        $phpseclib_path = __DIR__ . '/../vendor/phpseclib/phpseclib';
        
        if (!is_dir($phpseclib_path)) {
            mtrace("ERROR: phpseclib directory not found");
            return false;
        }
        
        // Registra autoloader personalizzato
        spl_autoload_register(function($class) use ($phpseclib_path) {
            
            // Solo per classi phpseclib3
            if (strpos($class, 'phpseclib3\\') !== 0) {
                return false;
            }
            
            // Converti namespace in percorso file
            $relative_class = substr($class, strlen('phpseclib3\\'));
            $file = $phpseclib_path . '/' . str_replace('\\', '/', $relative_class) . '.php';
            
            if (file_exists($file)) {
                require_once($file);
                return true;
            }
            
            return false;
        });
        
        mtrace("Custom autoloader registered for phpseclib3");
        
        // Test dell'autoloader
        try {
            if (class_exists('phpseclib3\\Net\\SFTP')) {
                mtrace("Custom autoloader test: SUCCESS");
                return true;
            } else {
                mtrace("Custom autoloader test: FAILED");
                return false;
            }
        } catch (\Exception $e) {
            mtrace("Custom autoloader test error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Test SFTP connection
     */
    public function test_connection($host, $port, $username, $password) {
        
        mtrace("=== TESTING SFTP CONNECTION (phpseclib autoloader) ===");
        
        // Setup autoloader
        if (!$this->setup_composer_autoloader()) {
            mtrace("ERROR: Cannot setup autoloader for testing");
            return false;
        }
        
        // Verifica classe disponibile
        if (!class_exists('phpseclib3\\Net\\SFTP')) {
            mtrace("ERROR: SFTP class not available for testing");
            return false;
        }
        
        try {
            $sftp = new \phpseclib3\Net\SFTP($host, $port);
            
            $result = $sftp->login($username, $password);
            
            if ($result) {
                mtrace("=== CONNECTION TEST SUCCESS ===");
                return true;
            } else {
                mtrace("=== CONNECTION TEST FAILED: Authentication failed ===");
                return false;
            }
            
        } catch (\Exception $e) {
            mtrace("=== CONNECTION TEST FAILED: " . $e->getMessage() . " ===");
            return false;
        }
    }
}