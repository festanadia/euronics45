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

namespace local_ard_report\reportbuilder\local\entities;

use context_course;
use context_helper;
use context_system;
use html_writer;
use lang_string;
use moodle_url;
use stdClass;
use core_reportbuilder\local\entities\base;
use core_reportbuilder\local\entities\{user};
use core_reportbuilder\local\filters\{select, text};
use core_reportbuilder\local\report\{column, filter};
use core_reportbuilder\local\helpers\format;

defined('MOODLE_INTERNAL') or die;

global $CFG;

/**
 * Badge entity
 *
 * @package     core_badges
 * @copyright   2022 Paul Holden <paulh@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_report_aggiornamento2023 extends base {

    /**
     * Database tables that this entity uses and their default aliases
     *
     * @return array
     */
    protected function get_default_table_aliases(): array {
        return [
            'local_report_aggiornamento2023' => 'eur',
        ];
    }

    /**
     * The default title for this entity
     *
     * @return lang_string
     */
    protected function get_default_entity_title(): lang_string {
        return new lang_string('local_report_aggiornamento2023', 'local_ard_report');
    }

    /**
     * Initialise the entity
     *
     * @return base
     */
    public function initialise(): base {
		
/*		global $USER;

        // Join the user
        $userentity = new user();
        $useralias = $userentity->get_table_alias('user');
        $this->add_entity($userentity
            ->add_join("INNER JOIN {user} {$useralias}
                ON {$useralias}.username = {$reportalias}.utente AND {$useralias}.username='admin.euronics' ")
        );
*/		
        $columns = $this->get_all_columns();
        foreach ($columns as $column) {
            $this->add_column($column);
        }


        // All the filters defined by the entity can also be used as conditions.
        $filters = $this->get_all_filters();
        foreach ($filters as $filter) {
            $this
                ->add_filter($filter)
                ->add_condition($filter);
        }

        return $this;
    }

    /**
     * Returns list of all available columns
     *
     * @return column[]
     */
    protected function get_all_columns(): array {
        global $DB,$USER;

        $reportalias = $this->get_table_alias('local_report_aggiornamento2023');

        // AziendaSocia
        $columns[] = (new column(
            'aziendasocia',
            new lang_string('aziendasocia', 'local_ard_report'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_field("{$reportalias}.aziendasocia")
            ->add_callback(static function(?string $name, stdClass $reportalias): string { 
                return format_string($reportalias->aziendasocia);
            })			
            ->set_is_sortable(true);

        // PuntoVendita
        $columns[] = (new column(
            'puntovendita',
            new lang_string('puntovendita', 'local_ard_report'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_field("{$reportalias}.puntovendita")
            ->add_callback(static function(?string $name, stdClass $reportalias): string { 
                return format_string($reportalias->puntovendita);
            })			
            ->set_is_sortable(true);
	
        // Utente
        $columns[] = (new column(
            'utente',
            new lang_string('utente', 'local_ard_report'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())		
            ->set_type(column::TYPE_TEXT)
            ->add_field("{$reportalias}.utente")
            ->add_callback(static function(?string $name, stdClass $reportalias): string { 
                return format_string($reportalias->utente);
            })			
            ->set_is_sortable(true);
			
        // DataCompletamentoCorso
        $columns[] = (new column(
            'datacompletamentocorso',
            new lang_string('datacompletamentocorso', 'local_ard_report'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_field("{$reportalias}.datacompletamentocorso")
            ->add_callback(static function(?string $name, stdClass $reportalias): string { 
                return format_string($reportalias->datacompletamentocorso);
            })			
            ->set_is_sortable(true);

        // Punteggio
        $columns[] = (new column(
            'punteggio',
            new lang_string('punteggio', 'local_ard_report'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_field("{$reportalias}.punteggio")
            ->add_callback(static function(?string $name, stdClass $reportalias): string { 
                return format_string($reportalias->punteggio);
            })			
            ->set_is_sortable(true);

        // Tempo Totale
        $columns[] = (new column(
            'tempototale',
            new lang_string('tempototale', 'local_ard_report'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_field("{$reportalias}.tempototale")
            ->add_callback(static function(?string $name, stdClass $reportalias): string { 
                return format_string($reportalias->tempototale);
            })			
            ->set_is_sortable(true);

        // Visualizza Certificato
        $columns[] = (new column(
            'visualizzacertificato',
            new lang_string('visualizzacertificato', 'local_ard_report'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_field("{$reportalias}.visualizzacertificato")			
            ->set_is_sortable(true);

        return $columns;
    }
	
	
	
	
    /**
     * Return list of all available filters
     *
     * @return filter[]
     */
    protected function get_all_filters(): array {
        $entityalias = $this->get_table_alias('local_report_aggiornamento2023');

        // AziendaSocia
        $filters[] = (new filter(
            text::class,
            'aziendasocia',
            new lang_string('aziendasocia', 'local_ard_report'),
            $this->get_entity_name(),
            "{$entityalias}.aziendasocia"
        ));

        // PuntoVendita
        $filters[] = (new filter(
            text::class,
            'puntovendita',
            new lang_string('puntovendita', 'local_ard_report'),
            $this->get_entity_name(),
            "{$entityalias}.puntovendita"
        ));

        // Utente
        $filters[] = (new filter(
            text::class,
            'utente',
            new lang_string('utente', 'local_ard_report'),
            $this->get_entity_name(),
            "{$entityalias}.utente"
        ));
		
        // Data Completamento Corso
        $filters[] = (new filter(
            text::class,
            'datacompletamentocorso',
            new lang_string('datacompletamentocorso', 'local_ard_report'),
            $this->get_entity_name(),
            "{$entityalias}.datacompletamentocorso"
        ));
					
        // Punteggio
        $filters[] = (new filter(
            text::class,
            'punteggio',
            new lang_string('punteggio', 'local_ard_report'),
            $this->get_entity_name(),
            "{$entityalias}.punteggio"
        ));
			
        // Tempo Totale
        $filters[] = (new filter(
            text::class,
            'tempototale',
            new lang_string('tempototale', 'local_ard_report'),
            $this->get_entity_name(),
            "{$entityalias}.tempototale"
        ));			
		
        return $filters;

		
    }
}
