<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Scheduled task: sync safety certificates to the SFTP server.
 *
 * For each record in the report tables, this task:
 * 1. Resolves the company (aziendasocia) to an SFTP path.
 * 2. Looks up the user's fiscal code (idnumber) from mdl_user.
 * 3. Downloads the certificate PDF from the URL in visualizzacertificato.
 * 4. Uploads it to the SFTP server as USERNAME-CODICEFISCALE.pdf.
 * 5. Skips the record if the file already exists on SFTP.
 *
 * Source tables and destination sub-folders:
 * - GENERALE:      local_report_certificato_sicurezza_12, local_report_certificato_sicurezza_22
 * - SPECIFICA:     local_report_specifica
 * - AGGIORNAMENTO: local_report_aggiornamento, local_report_aggiornamento2023
 *
 * @package    local_sftp_certificati
 * @copyright  2026 Euronics
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_sftp_certificati\task;

use local_sftp_certificati\helper;

defined('MOODLE_INTERNAL') || die();

/**
 * Scheduled task that uploads safety certificates to SFTP.
 */
class sync_certificates extends \core\task\scheduled_task {

    /** @var string|null Path to the temporary cookie file used for Moodle authentication. */
    private ?string $cookiefile = null;

    /**
     * Return the task name shown in the admin UI.
     *
     * @return string
     */
    public function get_name(): string {
        return get_string('task_sync_certificates', 'local_sftp_certificati');
    }

    /**
     * Execute the task.
     */
    public function execute(): void {
        global $CFG;

        mtrace('SFTP certificate sync — starting.');

        // 1. Build company map: aziendasocia code → SFTP base path.
        $companymap = $this->build_company_map();
        if (empty($companymap)) {
            mtrace('No companies configured. Aborting.');
            return;
        }
        mtrace('Company map: ' . count($companymap) . ' code(s) configured.');

        // 2. Verify phpseclib3 availability.
        if (!class_exists('\phpseclib3\Net\SFTP')) {
            mtrace('phpseclib3 is not available. Cannot connect to SFTP. Aborting.');
            return;
        }

        // 3. Connect to SFTP.
        $sftp = $this->connect_sftp();
        if ($sftp === null) {
            return;
        }

        // 4. Authenticate with Moodle (curl login) for certificate downloads.
        $curl = $this->authenticate_moodle();
        if ($curl === null) {
            $sftp->disconnect();
            return;
        }

        // 5. Process each certificate type.
        $stats = ['processed' => 0, 'uploaded' => 0, 'skipped' => 0, 'errors' => 0];

        foreach (helper::TABLE_MAP as $subfolder => $tables) {
            foreach ($tables as $table) {
                mtrace("Processing table {$table} → {$subfolder}");
                $this->process_table($table, $subfolder, $companymap, $sftp, $curl, $stats);
            }
        }

        // 6. Cleanup.
        $sftp->disconnect();
        if ($this->cookiefile && file_exists($this->cookiefile)) {
            @unlink($this->cookiefile);
        }

        mtrace(sprintf(
            'Sync complete. Processed: %d | Uploaded: %d | Skipped (existing): %d | Errors: %d',
            $stats['processed'],
            $stats['uploaded'],
            $stats['skipped'],
            $stats['errors']
        ));
    }

    // ------------------------------------------------------------------
    // Company map.
    // ------------------------------------------------------------------

    /**
     * Build the company map from plugin settings.
     *
     * @return array<string, string> aziendasocia code => SFTP base path (no trailing slash).
     */
    protected function build_company_map(): array {
        $map = [];
        foreach (helper::COMPANIES as $key => $name) {
            $codes = get_config('local_sftp_certificati', 'aziendasocia_' . $key);
            $path  = get_config('local_sftp_certificati', 'sftp_path_' . $key);
            if (empty($codes) || empty($path)) {
                continue;
            }
            $path = rtrim($path, '/');
            foreach (explode(',', $codes) as $code) {
                $code = trim($code);
                if ($code !== '') {
                    $map[$code] = $path;
                }
            }
        }
        return $map;
    }

    // ------------------------------------------------------------------
    // SFTP connection.
    // ------------------------------------------------------------------

    /**
     * Connect and authenticate to the SFTP server.
     *
     * @return \phpseclib3\Net\SFTP|null Connected SFTP instance, or null on failure.
     */
    protected function connect_sftp(): ?\phpseclib3\Net\SFTP {
        global $CFG;

        $host       = get_config('local_sftp_certificati', 'sftp_host');
        $port       = (int) get_config('local_sftp_certificati', 'sftp_port') ?: 22;
        $username   = get_config('local_sftp_certificati', 'sftp_username');
        $keyfile    = get_config('local_sftp_certificati', 'sftp_keyfile');
        $passphrase = get_config('local_sftp_certificati', 'sftp_keypassphrase');

        if (empty($host) || empty($username) || empty($keyfile)) {
            mtrace('SFTP connection settings incomplete (host/username/keyfile). Aborting.');
            return null;
        }

        $keypath = $CFG->dirroot . '/local/sftp_certificati/keys/' . $keyfile;
        if (!file_exists($keypath)) {
            mtrace("PPK key file not found: {$keypath}. Aborting.");
            return null;
        }

        try {
            $keycontents = file_get_contents($keypath);
            $key = \phpseclib3\Crypt\PublicKeyLoader::load(
                $keycontents,
                $passphrase !== '' ? $passphrase : false
            );

            $sftp = new \phpseclib3\Net\SFTP($host, $port);
            if (!$sftp->login($username, $key)) {
                mtrace('SFTP authentication failed (login returned false).');
                return null;
            }

            mtrace("SFTP connected to {$host}:{$port} as {$username}.");
            return $sftp;
        } catch (\Exception $e) {
            mtrace('SFTP connection error: ' . $e->getMessage());
            return null;
        }
    }

    // ------------------------------------------------------------------
    // Moodle authentication (curl‑based login).
    // ------------------------------------------------------------------

    /**
     * Log in to Moodle via HTTP and return an authenticated curl instance.
     *
     * The curl instance carries session cookies so that subsequent GETs
     * to certificate URLs return the PDF content.
     *
     * @return \curl|null Authenticated curl instance, or null on failure.
     */
    protected function authenticate_moodle(): ?\curl {
        global $CFG;

        $username = get_config('local_sftp_certificati', 'moodle_auth_user');
        $password = get_config('local_sftp_certificati', 'moodle_auth_pass');

        if (empty($username) || empty($password)) {
            mtrace('Moodle auth settings incomplete (username/password). Aborting.');
            return null;
        }

        // Prepare cookie jar.
        $this->cookiefile = make_temp_directory('local_sftp_certificati')
            . '/cookies_' . getmypid() . '.txt';

        try {
            $curl = new \curl(['cookie' => $this->cookiefile]);

            // Step 1 — GET the login page to obtain the logintoken.
            $loginurl  = $CFG->wwwroot . '/login/index.php';
            $loginpage = $curl->get($loginurl, [],
                ['CURLOPT_FOLLOWLOCATION' => true, 'CURLOPT_MAXREDIRS' => 5]);

            if (!preg_match('/<input[^>]+name="logintoken"[^>]+value="([^"]+)"/', $loginpage, $m)
                && !preg_match('/<input[^>]+value="([^"]+)"[^>]+name="logintoken"/', $loginpage, $m)) {
                mtrace('Could not extract logintoken from login page.');
                return null;
            }
            $logintoken = $m[1];

            // Step 2 — POST credentials.
            $curl->post($loginurl, [
                'username'   => $username,
                'password'   => $password,
                'logintoken' => $logintoken,
            ], ['CURLOPT_FOLLOWLOCATION' => true, 'CURLOPT_MAXREDIRS' => 5]);

            // Step 3 — Verify: try accessing /my/ — if we still see a logintoken
            //          in the response the login failed.
            $testpage = $curl->get($CFG->wwwroot . '/my/', [],
                ['CURLOPT_FOLLOWLOCATION' => true, 'CURLOPT_MAXREDIRS' => 5]);

            if (strpos($testpage, 'logintoken') !== false) {
                mtrace('Moodle login failed — credentials may be incorrect.');
                return null;
            }

            mtrace('Moodle authentication successful.');
            return $curl;
        } catch (\Exception $e) {
            mtrace('Moodle authentication error: ' . $e->getMessage());
            return null;
        }
    }

    // ------------------------------------------------------------------
    // Record processing.
    // ------------------------------------------------------------------

    /**
     * Process all records from a single report table.
     *
     * @param string                 $table      Moodle table name (without prefix).
     * @param string                 $subfolder  GENERALE | SPECIFICA | AGGIORNAMENTO.
     * @param array                  $companymap aziendasocia code => SFTP base path.
     * @param \phpseclib3\Net\SFTP   $sftp       Connected SFTP instance.
     * @param \curl                  $curl       Authenticated curl instance.
     * @param array                  $stats      Reference to stats counters.
     */
    protected function process_table(
        string $table,
        string $subfolder,
        array $companymap,
        \phpseclib3\Net\SFTP $sftp,
        \curl $curl,
        array &$stats
    ): void {
        global $DB;

        try {
            $sql = "SELECT id, aziendasocia, utente, visualizzacertificato
                      FROM {{$table}}";
            $records = $DB->get_records_sql($sql);
        } catch (\Exception $e) {
            mtrace("  ERROR querying {$table}: " . $e->getMessage());
            return;
        }

        if (empty($records)) {
            mtrace("  No records in {$table}.");
            return;
        }

        mtrace('  ' . count($records) . ' record(s) found.');

        foreach ($records as $record) {
            $stats['processed']++;
            $result = $this->process_record($record, $subfolder, $companymap, $sftp, $curl);
            $stats[$result]++;
        }
    }

    /**
     * Process a single certificate record.
     *
     * @param object                $record     DB record with aziendasocia, utente, visualizzacertificato.
     * @param string                $subfolder  Target sub-folder (GENERALE, SPECIFICA, AGGIORNAMENTO).
     * @param array                 $companymap aziendasocia code => SFTP base path.
     * @param \phpseclib3\Net\SFTP  $sftp       Connected SFTP instance.
     * @param \curl                 $curl       Authenticated curl instance.
     * @return string 'uploaded' | 'skipped' | 'errors'
     */
    protected function process_record(
        object $record,
        string $subfolder,
        array $companymap,
        \phpseclib3\Net\SFTP $sftp,
        \curl $curl
    ): string {
        global $DB, $CFG;

        $aziendasocia = trim($record->aziendasocia ?? '');
        $utente       = trim($record->utente ?? '');
        $certhtml     = $record->visualizzacertificato ?? '';

        // 1. Resolve company → SFTP base path.
        if (!isset($companymap[$aziendasocia])) {
            mtrace("    [SKIP] id={$record->id}: unknown aziendasocia '{$aziendasocia}'.");
            return 'errors';
        }
        $basepath = $companymap[$aziendasocia];

        // 2. Look up user's fiscal code.
        if (empty($utente)) {
            mtrace("    [SKIP] id={$record->id}: empty utente field.");
            return 'errors';
        }

        $user = $DB->get_record('user', ['username' => strtolower($utente)], 'id, username, idnumber');
        if (!$user) {
            mtrace("    [SKIP] id={$record->id}: user '{$utente}' not found in mdl_user.");
            return 'errors';
        }

        $codicefiscale = strtoupper(trim($user->idnumber ?? ''));
        if (empty($codicefiscale)) {
            mtrace("    [SKIP] id={$record->id}: user '{$utente}' has no idnumber (codice fiscale).");
            return 'errors';
        }

        // 3. Build filename: USERNAME-CODICEFISCALE.pdf (uppercase).
        $utenteUpper = strtoupper($utente);
        $filename    = $utenteUpper . '-' . $codicefiscale . '.pdf';

        // 4. Build remote path.
        $remotedir  = $basepath . '/CERTIFICATI_SICUREZZA/' . $subfolder;
        $remotepath = $remotedir . '/' . $filename;

        // 5. Skip if file already exists on SFTP.
        if ($sftp->stat($remotepath) !== false) {
            return 'skipped';
        }

        // 6. Extract URL from the HTML link.
        $url = $this->extract_certificate_url($certhtml);
        if (empty($url)) {
            mtrace("    [SKIP] id={$record->id}: could not parse certificate URL.");
            return 'errors';
        }

        // Make URL absolute if needed.
        if (strpos($url, 'http') !== 0) {
            $url = $CFG->wwwroot . '/' . ltrim($url, '/');
        }

        // 7. Download the certificate PDF.
        $pdfcontent = $this->download_certificate($curl, $url);
        if ($pdfcontent === null) {
            mtrace("    [SKIP] id={$record->id}: failed to download PDF from {$url}.");
            return 'errors';
        }

        // 8. Ensure remote directory exists and upload.
        $this->ensure_remote_directory($sftp, $remotedir);

        if (!$sftp->put($remotepath, $pdfcontent)) {
            mtrace("    [ERROR] id={$record->id}: SFTP put failed for {$remotepath}.");
            return 'errors';
        }

        mtrace("    [OK] {$filename} → {$remotepath}");
        return 'uploaded';
    }

    // ------------------------------------------------------------------
    // Utility helpers.
    // ------------------------------------------------------------------

    /**
     * Extract the certificate URL from an HTML anchor tag.
     *
     * Input example:
     * <a href='https://…/issuecertificate.php?id=187&amp;userid=1719&o=P' …>…</a>
     *
     * @param string $html Raw HTML string.
     * @return string Decoded URL, or empty string on failure.
     */
    protected function extract_certificate_url(string $html): string {
        if (empty($html)) {
            return '';
        }
        // Match href with either single or double quotes.
        if (!preg_match('/href=[\'"]([^\'"]+)[\'"]/', $html, $matches)) {
            return '';
        }
        return html_entity_decode($matches[1], ENT_QUOTES, 'UTF-8');
    }

    /**
     * Download a certificate PDF via the authenticated curl session.
     *
     * @param \curl  $curl Authenticated curl instance.
     * @param string $url  Absolute URL to the certificate.
     * @return string|null PDF binary content, or null on failure.
     */
    protected function download_certificate(\curl $curl, string $url): ?string {
        try {
            $content = $curl->get($url, [], [
                'CURLOPT_FOLLOWLOCATION' => true,
                'CURLOPT_MAXREDIRS'      => 5,
                'CURLOPT_TIMEOUT'        => 60,
            ]);

            if (empty($content)) {
                return null;
            }

            // Basic sanity check: a PDF starts with %PDF.
            if (strpos($content, '%PDF') !== 0) {
                mtrace("      WARNING: response does not start with %PDF header.");
                return null;
            }

            return $content;
        } catch (\Exception $e) {
            mtrace('      Download error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Recursively create a directory on the SFTP server if it does not exist.
     *
     * @param \phpseclib3\Net\SFTP $sftp SFTP instance.
     * @param string               $path Remote directory path.
     */
    protected function ensure_remote_directory(\phpseclib3\Net\SFTP $sftp, string $path): void {
        if ($sftp->stat($path) !== false) {
            return;
        }
        $sftp->mkdir($path, -1, true);
    }
}
