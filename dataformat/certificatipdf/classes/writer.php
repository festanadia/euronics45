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
 * PDF data format writer che ora unisce i PDF remoti indicati nell’ultima colonna
 *
 * @package    dataformat_certificatipdf
 * @copyright  2019 Shamim Rezaie <shamim@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dataformat_certificatipdf;

global $CFG;
require_once($CFG->libdir.'/pdflib.php');
require_once($CFG->dirroot.'/dataformat/certificatipdf/fpdi/autoload.php');

defined('MOODLE_INTERNAL') || die();

use setasign\Fpdi\Tcpdf\Fpdi;

class writer extends \core\dataformat\base {

    public $mimetype = "application/pdf";

    public $extension = ".pdf";

    /**
     * @var Fpdi L’oggetto PDF usato per generare il file finale.
     */
    protected $pdf;

    /**
     * writer constructor.
     */
    public function __construct() {
        global $CFG;
        require_once($CFG->libdir . '/pdflib.php');
        // Istanziamo FPDI (che estende TCPDF).
        $this->pdf = new \setasign\Fpdi\Tcpdf\Fpdi();

        // Disattiviamo header e footer.
        $this->pdf->setPrintHeader(false);
        $this->pdf->setPrintFooter(false);

        // Impostiamo i margini a zero (left, top, right).
        $this->pdf->SetMargins(0, 0, 0);

        // Disabilitiamo l'auto page break e impostiamo il margine inferiore a zero.
        $this->pdf->SetAutoPageBreak(false, 0);

        // Impostiamo il margine del footer a zero, se utilizzato.
        $this->pdf->SetFooterMargin(0);

        // Eventuale settaggio del colore di riempimento (opzionale).
        $this->pdf->SetFillColor(238, 238, 238);
    }

    public function send_http_headers() {
        // I header HTTP verranno inviati dal core di Moodle.
    }

    /**
     * Inizializza l’output. Per l’unione dei PDF non è necessario aggiungere una prima pagina.
     */
    public function start_output_to_file(): void {
        $this->start_output();
    }

    public function start_output() {
        // Non facciamo nulla in quanto le pagine saranno aggiunte in write_record().
    }

    /**
     * In fase di start_sheet non è necessario stampare intestazioni o gestire colonne,
     * quindi questo metodo viene lasciato vuoto.
     *
     * @param array $columns
     */
    public function start_sheet($columns) {
        // Non è necessario per l'unione dei PDF.
    }

    /**
     * Indica se il formato dati supporta l’export in HTML.
     *
     * @return bool
     */
    public function supports_html(): bool {
        return true;
    }

    /**
     * Se si esportano immagini, viene restituito il contenuto in Base64.
     *
     * @param \stored_file $file
     * @return string|null
     */
    protected function export_html_image_source(\stored_file $file): ?string {
        // Imposta dimensioni massime per le immagini incorporate.
        $resizedimage = $file->resize_image(400, 300);
        return '@' . base64_encode($resizedimage);
    }

    /**
     * Per ogni record (riga del report) si prevede che l’ultima colonna contenga un URL che punta ad un PDF remoto.
     * Il metodo recupera il PDF remoto e, usando FPDI, importa tutte le sue pagine nel documento finale.
     *
     * In questa versione:
     * - Dall'URL viene estratto il parametro "o" (che può essere "L" o "P"); se non specificato, si usa "L" di default.
     * - Per la prima riga, si estrae anche il parametro "id" (che ora corrisponde all'id di mdl_course_modules)
     *   per recuperare il record del course e, da lì, il fullname da usare come parte del nome file.
     * - Al nome file viene aggiunto anche il valore "institution" dell'utente in sessione e la data corrente.
     *
     * @param array $record I dati del record (la formattazione eventualmente già applicata da format_record()).
     * @param int $rownum Numero della riga (se è la prima riga viene generato il nome file).
     */
    public function write_record($record, $rownum) {
        // Applichiamo eventuali formattazioni sul record.
        $record = $this->format_record($record);

        // Recuperiamo l'URL presente nell'ultima colonna.
        $pdfurl = end($record);
        reset($record);

        // Estrarre l'URL se formattato come tag <a>.
        $pdfurl = $this->extractUrl($pdfurl);

        // Decodifica eventuali entità HTML per ottenere l'URL corretto.
        $pdfurl = html_entity_decode($pdfurl, ENT_QUOTES, 'UTF-8');

        // Recupera i parametri della query string.
        $parsedUrl = parse_url($pdfurl);
        $queryParams = [];
        if (isset($parsedUrl['query'])) {
            parse_str($parsedUrl['query'], $queryParams);
        }

        // Recupera il parametro "o" dall'URL, se presente e valido ("L" o "P").
        // Se non è presente, di default usiamo "L".
        $orientation = 'L';
        if (isset($queryParams['o']) && in_array($queryParams['o'], ['L', 'P'])) {
            $orientation = $queryParams['o'];
        }

        // Se siamo sulla prima riga e il parametro "id" è presente,
        // usiamo tale id (id della course module) per generare il nome file.
        // Recuperiamo il record di course_modules e poi quello del corso.
        if ($rownum == 0 && isset($queryParams['id'])) {
            global $DB, $USER;
            $cmid = $queryParams['id'];
            if ($cm = $DB->get_record('course_modules', array('id' => $cmid))) {
                if (!empty($cm->course)) {
                    if ($course = $DB->get_record('course', array('id' => $cm->course))) {
                        $coursename = $course->fullname;
                        // Pulisce il nome per renderlo valido come filename.
                        $coursename = preg_replace('/[^a-zA-Z0-9-_\.]/', '_', $coursename);
                        // Recupera il valore institution dell'utente in sessione (se presente).
                        $institution = '';
                        if (!empty($USER->institution)) {
                            $institution = preg_replace('/[^a-zA-Z0-9-_\.]/', '_', $USER->institution);
                        }
                        // Recupera la data corrente (formattata ad es. come YYYYMMDD).
                        $date = date('Ymd');
                        // Combina le parti per formare il nome file.
                        $this->filename = $coursename . '_' . $institution . '_' . $date;
                    }
                }
            }
        }



        try {
			

			// Scarichiamo il PDF remoto.

			
			if (isset($pdfurl)) $pdfdata = file_get_contents($pdfurl);
			
			if ($pdfdata === false) {
				return;
			}

			// Salviamo il contenuto in un file temporaneo.
			$tmpfile = tempnam(sys_get_temp_dir(), 'pdfmerge_');
			file_put_contents($tmpfile, $pdfdata);
		
            // Impostiamo il file sorgente per FPDI.
            $pageCount = $this->pdf->setSourceFile($tmpfile);
            // Per ogni pagina del PDF remoto…
            for ($i = 1; $i <= $pageCount; $i++) {
                // Importiamo la pagina.
                $tplId = $this->pdf->importPage($i);
                $size = $this->pdf->getTemplateSize($tplId);
                // Aggiungiamo una nuova pagina al documento finale con le dimensioni della pagina importata.
                $this->pdf->AddPage($orientation, [$size['w'], $size['h']]);
                // Inseriamo la pagina importata.
                $this->pdf->useTemplate($tplId);
            }
        } catch (\Exception $e) {
            return;
        }

        // Puliamo il file temporaneo.
        unlink($tmpfile);
    }

    /**
     * Quando l’output è completato, il file PDF unito viene inviato al browser per il download.
     * Se non sono state aggiunte pagine (cioè non ci sono certificati da scaricare), viene mostrato un messaggio di errore.
     */
    public function close_output() {
        if ($this->pdf->getNumPages() < 1) {
            echo "Il report non contiene certificati da scaricare.";
            exit;
        }
        $filename = $this->filename . $this->get_extension();
        $this->pdf->Output($filename, 'D');
    }

    /**
     * Scrive il file su disco.
     *
     * @return bool
     */
    public function close_output_to_file(): bool {
        if ($this->pdf->getNumPages() < 1) {
            echo "Il report non contiene certificati da scaricare.";
            exit;
        }
        $this->pdf->Output($this->filepath, 'F');
        return true;
    }

    /**
     * Estrae l'URL dall'input. Se l'input è un tag <a href="...">, restituisce il valore dell'attributo href,
     * altrimenti restituisce l'input inalterato.
     *
     * @param string $input L'input contenente l'URL o il tag HTML.
     * @return string L'URL estratto.
     */
    private function extractUrl(string $input): string {
        // Decodifica eventuali entità HTML.
        $input = html_entity_decode($input, ENT_QUOTES, 'UTF-8');

        // Se trova il tag <a, cerca di estrarre l'attributo href.
        if (stripos($input, '<a') !== false) {
            if (preg_match('/<a\s+href=["\']([^"\']+)["\'].*?>/i', $input, $matches)) {
                return $matches[1];
            }
        }
        return $input;
    }

    // I metodi precedenti relativi alla stampa della tabella (print_heading, get_heading_height, ecc.)
    // non sono più necessari e sono stati omessi.
}
