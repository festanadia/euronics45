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

declare(strict_types=1);

namespace local_ard_report\reportbuilder\datasource;

use lang_string;
use core_reportbuilder\datasource;
use local_ard_report\reportbuilder\local\entities\{local_report_certificato_sicurezza_12};

/**
 * Badges datasource
 *
 * @package     core_badges
 * @copyright   2022 Paul Holden <paulh@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_report_certificato_sicurezza_12s extends datasource {

    /**
     * Return user friendly name of the report source
     *
     * @return string
     */
    public static function get_name(): string {
        return get_string('certificato_sicurezza_12', 'local_ard_report');
    }

    /**
     * Initialise report
     */
    protected function initialise(): void {
        $eur = new local_report_certificato_sicurezza_12();
        $reportalias = $eur->get_table_alias('local_report_certificato_sicurezza_12');

        $this->set_main_table('local_report_certificato_sicurezza_12', $reportalias);
        $this->add_entity($eur);

        // Add report elements from each of the entities we added to the report.
        $this->add_all_from_entities();
    }

    /**
     * Return the columns that will be added to the report upon creation
     *
     * @return string[]
     */
    public function get_default_columns(): array {
        return [
            'local_report_certificato_sicurezza_12:aziendasocia',	
            'local_report_certificato_sicurezza_12:puntovendita',
            'local_report_certificato_sicurezza_12:nome',
            'local_report_certificato_sicurezza_12:cognome',		
			'local_report_certificato_sicurezza_12:datacompletamentocorso',
			'local_report_certificato_sicurezza_12:utente',
			'local_report_certificato_sicurezza_12:punteggio',			
			'local_report_certificato_sicurezza_12:visualizzacertificato',
        ];
    }

    /**
     * Return the filters that will be added to the report upon creation
     *
     * @return string[]
     */
    public function get_default_filters(): array {
		global $USER;
		
        return [	
        ];
    }

    /**
     * Return the conditions that will be added to the report upon creation
     *
     * @return string[]
     */
    public function get_default_conditions(): array {
        return [
        ];
    }

    /**
     * Return the default sorting that will be added to the report once it is created
     *
     * @return array|int[]
     */
    public function get_default_column_sorting(): array {
        return [
            'local_report_certificato_sicurezza_12:aziendasocia' => SORT_ASC,
            'local_report_certificato_sicurezza_12:puntovendita' => SORT_ASC,
			'local_report_certificato_sicurezza_12:nome' => SORT_ASC,
			'local_report_certificato_sicurezza_12:cognome' => SORT_ASC,		
			'local_report_certificato_sicurezza_12:datacompletamentocorso' => SORT_ASC,			
			'local_report_certificato_sicurezza_12:utente' => SORT_ASC,
			'local_report_certificato_sicurezza_12:punteggio' => SORT_ASC,					
			'local_report_certificato_sicurezza_12:visualizzacertificato' => SORT_ASC,				
        ];
    }
}
