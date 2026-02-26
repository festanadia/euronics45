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
 * Helper class with shared constants and utilities.
 *
 * @package    local_sftp_certificati
 * @copyright  2026 Euronics
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_sftp_certificati;

defined('MOODLE_INTERNAL') || die();

/**
 * Helper class for the SFTP Certificati plugin.
 */
class helper {

    /** @var array Partner companies: settings key => display name. */
    const COMPANIES = [
        'bruno_spa'       => 'BRUNO SPA',
        'butali_spa'      => 'BUTALI SPA',
        'dimo_spa'        => 'DIMO SPA',
        'lavialattea_spa' => 'LA VIA LATTEA SPA',
        'rimep_spa'       => 'RIMEP SPA',
        'siem_spa'        => 'SIEM SPA',
        'tufano_spa'      => 'TUFANO SPA',
        'comet'           => 'COMET',
        'sme'             => 'SME',
    ];

    /** @var array Certificate type → source tables mapping. */
    const TABLE_MAP = [
        'GENERALE' => [
            'local_report_certificato_sicurezza_12',
            'local_report_certificato_sicurezza_22',
        ],
        'SPECIFICA' => [
            'local_report_specifica',
        ],
        'AGGIORNAMENTO' => [
            'local_report_aggiornamento',
            'local_report_aggiornamento2023',
        ],
    ];
}
