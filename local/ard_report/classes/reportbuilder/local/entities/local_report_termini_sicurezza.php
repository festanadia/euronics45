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
class local_report_termini_sicurezza extends base {

    /**
     * Database tables that this entity uses and their default aliases
     *
     * @return array
     */
    protected function get_default_table_aliases(): array {
        return [
            'local_report_termini_sicurezza' => 'eur',
        ];
    }

    /**
     * The default title for this entity
     *
     * @return lang_string
     */
    protected function get_default_entity_title(): lang_string {
        return new lang_string('local_report_termini_sicurezza', 'local_ard_report');
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

        $reportalias = $this->get_table_alias('local_report_termini_sicurezza');

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
	
// Tipologia
$columns[] = (new column(
    'tipologia',
    new lang_string('tipologia', 'local_ard_report'),
    $this->get_entity_name()
))
    ->add_joins($this->get_joins())
    ->set_type(column::TYPE_TEXT)
    ->add_field("{$reportalias}.tipologia")
    ->add_callback(static function(?string $name, stdClass $reportalias): string {
        return format_string($reportalias->tipologia);
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
	
// Mansione
$columns[] = (new column(
    'mansione',
    new lang_string('mansione', 'local_ard_report'),
    $this->get_entity_name()
))
    ->add_joins($this->get_joins())
    ->set_type(column::TYPE_TEXT)
    ->add_field("{$reportalias}.mansione")
    ->add_callback(static function(?string $name, stdClass $reportalias): string {
        return format_string($reportalias->mansione);
    })
    ->set_is_sortable(true);

// Data Registrazione Generale
$columns[] = (new column(
    'reggenerale',
    new lang_string('reggenerale', 'local_ard_report'),
    $this->get_entity_name()
))
    ->add_joins($this->get_joins())
    ->set_type(column::TYPE_TEXT)
    ->add_field("{$reportalias}.reggenerale")
    ->add_callback(static function(?string $name, stdClass $reportalias): string {
        return format_string($reportalias->reggenerale);
    })
    ->set_is_sortable(true);

// Stato Generale
$columns[] = (new column(
    'statogenerale',
    new lang_string('statogenerale', 'local_ard_report'),
    $this->get_entity_name()
))
    ->add_joins($this->get_joins())
    ->set_type(column::TYPE_TEXT)
    ->add_field("{$reportalias}.statogenerale")
    ->add_callback(static function(?string $name, stdClass $reportalias): string {
        return format_string($reportalias->statogenerale);
    })
    ->set_is_sortable(true);

// Data Completamento Generale
$columns[] = (new column(
    'complgenerale',
    new lang_string('complgenerale', 'local_ard_report'),
    $this->get_entity_name()
))
    ->add_joins($this->get_joins())
    ->set_type(column::TYPE_TEXT)
    ->add_field("{$reportalias}.complgenerale")
    ->add_callback(static function(?string $name, stdClass $reportalias): string {
        return format_string($reportalias->complgenerale);
    })
    ->set_is_sortable(true);

// Data Registrazione Specifica
$columns[] = (new column(
    'regspecifica',
    new lang_string('regspecifica', 'local_ard_report'),
    $this->get_entity_name()
))
    ->add_joins($this->get_joins())
    ->set_type(column::TYPE_TEXT)
    ->add_field("{$reportalias}.regspecifica")
    ->add_callback(static function(?string $name, stdClass $reportalias): string {
        return format_string($reportalias->regspecifica);
    })
    ->set_is_sortable(true);

// Stato Specifica
$columns[] = (new column(
    'statospecifica',
    new lang_string('statospecifica', 'local_ard_report'),
    $this->get_entity_name()
))
    ->add_joins($this->get_joins())
    ->set_type(column::TYPE_TEXT)
    ->add_field("{$reportalias}.statospecifica")
    ->add_callback(static function(?string $name, stdClass $reportalias): string {
        return format_string($reportalias->statospecifica);
    })
    ->set_is_sortable(true);

// Data Completamento Specifica
$columns[] = (new column(
    'complspecifica',
    new lang_string('complspecifica', 'local_ard_report'),
    $this->get_entity_name()
))
    ->add_joins($this->get_joins())
    ->set_type(column::TYPE_TEXT)
    ->add_field("{$reportalias}.complspecifica")
    ->add_callback(static function(?string $name, stdClass $reportalias): string {
        return format_string($reportalias->complspecifica);
    })
    ->set_is_sortable(true);

// Scadenza Generale Specifica
$columns[] = (new column(
    'scadenzageneralespecifica',
    new lang_string('scadenzageneralespecifica', 'local_ard_report'),
    $this->get_entity_name()
))
    ->add_joins($this->get_joins())
    ->set_type(column::TYPE_TEXT)
    ->add_field("{$reportalias}.scadenzageneralespecifica")
    ->add_callback(static function(?string $name, stdClass $reportalias): string {
        return format_string($reportalias->scadenzageneralespecifica);
    })
    ->set_is_sortable(true);

// Data Registrazione Aggiornamento
$columns[] = (new column(
    'regaggiornamento',
    new lang_string('regaggiornamento', 'local_ard_report'),
    $this->get_entity_name()
))
    ->add_joins($this->get_joins())
    ->set_type(column::TYPE_TEXT)
    ->add_field("{$reportalias}.regaggiornamento")
    ->add_callback(static function(?string $name, stdClass $reportalias): string {
        return format_string($reportalias->regaggiornamento);
    })
    ->set_is_sortable(true);

// Stato Aggiornamento
$columns[] = (new column(
    'statoaggiornamento',
    new lang_string('statoaggiornamento', 'local_ard_report'),
    $this->get_entity_name()
))
    ->add_joins($this->get_joins())
    ->set_type(column::TYPE_TEXT)
    ->add_field("{$reportalias}.statoaggiornamento")
    ->add_callback(static function(?string $name, stdClass $reportalias): string {
        return format_string($reportalias->statoaggiornamento);
    })
    ->set_is_sortable(true);

// Completamento Aggiornamento
$columns[] = (new column(
    'complaggiornamento',
    new lang_string('complaggiornamento', 'local_ard_report'),
    $this->get_entity_name()
))
    ->add_joins($this->get_joins())
    ->set_type(column::TYPE_TEXT)
    ->add_field("{$reportalias}.complaggiornamento")
    ->add_callback(static function(?string $name, stdClass $reportalias): string {
        return format_string($reportalias->complaggiornamento);
    })
    ->set_is_sortable(true);

// Scadenza Aggiornamento
$columns[] = (new column(
    'scadenzaaggiornamento',
    new lang_string('scadenzaaggiornamento', 'local_ard_report'),
    $this->get_entity_name()
))
    ->add_joins($this->get_joins())
    ->set_type(column::TYPE_TEXT)
    ->add_field("{$reportalias}.scadenzaaggiornamento")
    ->add_callback(static function(?string $name, stdClass $reportalias): string {
        return format_string($reportalias->scadenzaaggiornamento);
    })
    ->set_is_sortable(true);






        return $columns;
    }
	
	
	
	
    /**
     * Return list of all available filters
     *
     * @return filter[]
     */
    protected function get_all_filters(): array {
        $entityalias = $this->get_table_alias('local_report_termini_sicurezza');

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

        // Tipologia
        $filters[] = (new filter(
            text::class,
            'tipologia',
            new lang_string('tipologia', 'local_ard_report'),
            $this->get_entity_name(),
            "{$entityalias}.tipologia"
        ));
		
        // Utente
        $filters[] = (new filter(
            text::class,
            'utente',
            new lang_string('utente', 'local_ard_report'),
            $this->get_entity_name(),
            "{$entityalias}.utente"
        ));
		
		
        return $filters;

		
    }
}
