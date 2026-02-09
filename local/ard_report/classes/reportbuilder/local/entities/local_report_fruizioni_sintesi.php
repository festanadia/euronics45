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
//require_once("{$CFG->libdir}/badgeslib.php");

/**
 * Badge entity
 *
 * @package     core_badges
 * @copyright   2022 Paul Holden <paulh@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_report_fruizioni_sintesi extends base {

    /**
     * Database tables that this entity uses and their default aliases
     *
     * @return array
     */
    protected function get_default_table_aliases(): array {
        return [
            'local_report_fruizioni_sintesi' => 'eur',
        ];
    }

    /**
     * The default title for this entity
     *
     * @return lang_string
     */
    protected function get_default_entity_title(): lang_string {
        return new lang_string('local_report_fruizioni_sintesi', 'local_ard_report');
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

        $reportalias = $this->get_table_alias('local_report_fruizioni_sintesi');

/*        // Visibilità
        $columns[] = (new column(
            'visibilita',
            new lang_string('visibilita', 'local_ard_report'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
			->add_join("INNER JOIN (
    SELECT '$USER->username' AS username
) AS filtro ON {$reportalias}.visibilita = filtro.username ")			
            ->set_type(column::TYPE_TEXT)
            ->add_field("{$reportalias}.visibilita")
            ->add_callback(static function(?string $name, stdClass $reportalias): string { 
                return format_string($reportalias->visibilita);
            })			
            ->set_is_sortable(true);
*/
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

        // CF
        $columns[] = (new column(
            'cf',
            new lang_string('cf', 'local_ard_report'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_field("{$reportalias}.cf")
            ->add_callback(static function(?string $name, stdClass $reportalias): string { 
                return format_string($reportalias->cf);
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

        // Corso
        $columns[] = (new column(
            'corso',
            new lang_string('corso', 'local_ard_report'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_field("{$reportalias}.corso")
            ->add_callback(static function(?string $name, stdClass $reportalias): string { 
                return format_string($reportalias->corso);
            })			
            ->set_is_sortable(true);

        // Categoria
        $columns[] = (new column(
            'categoria',
            new lang_string('categoria', 'local_ard_report'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_field("{$reportalias}.categoria")
            ->add_callback(static function(?string $name, stdClass $reportalias): string { 
                return format_string($reportalias->categoria);
            })			
            ->set_is_sortable(true);

        // DataRegistrazione
        $columns[] = (new column(
            'dataregistrazione',
            new lang_string('dataregistrazione', 'local_ard_report'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_field("{$reportalias}.dataregistrazione")
            ->add_callback(static function(?string $name, stdClass $reportalias): string { 
                return format_string($reportalias->dataregistrazione);
            })			
            ->set_is_sortable(true);

        // TipoRegistrazione
        $columns[] = (new column(
            'tiporegistrazione',
            new lang_string('tiporegistrazione', 'local_ard_report'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_field("{$reportalias}.tiporegistrazione")
            ->add_callback(static function(?string $name, stdClass $reportalias): string { 
                return format_string($reportalias->tiporegistrazione);
            })			
            ->set_is_sortable(true);

        // StatoCompletamentoCorso
        $columns[] = (new column(
            'statocompletamentocorso',
            new lang_string('statocompletamentocorso', 'local_ard_report'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_field("{$reportalias}.statocompletamentocorso")
            ->add_callback(static function(?string $name, stdClass $reportalias): string { 
                return format_string($reportalias->statocompletamentocorso);
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

        // PunteggioCorso
        $columns[] = (new column(
            'punteggiocorso',
            new lang_string('punteggiocorso', 'local_ard_report'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_field("{$reportalias}.punteggiocorso")
            ->add_callback(static function(?string $name, stdClass $reportalias): string { 
                return format_string($reportalias->punteggiocorso);
            })			
            ->set_is_sortable(true);

        // TempoTotale
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

        return $columns;
    }
	
	
	
	
    /**
     * Return list of all available filters
     *
     * @return filter[]
     */
    protected function get_all_filters(): array {
        $entityalias = $this->get_table_alias('local_report_fruizioni_sintesi');

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

        // Corso
        $filters[] = (new filter(
            text::class,
            'corso',
            new lang_string('corso', 'local_ard_report'),
            $this->get_entity_name(),
            "{$entityalias}.corso"
        ));
			
        // Categoria
        $filters[] = (new filter(
            text::class,
            'categoria',
            new lang_string('categoria', 'local_ard_report'),
            $this->get_entity_name(),
            "{$entityalias}.categoria"
        ));
			
        // Tipologia PV
        $filters[] = (new filter(
            text::class,
            'tipologia',
            new lang_string('tipologia', 'local_ard_report'),
            $this->get_entity_name(),
            "{$entityalias}.tipologia"
        ));
			
        // Brand
        $filters[] = (new filter(
            text::class,
            'brand',
            new lang_string('brand', 'local_ard_report'),
            $this->get_entity_name(),
            "{$entityalias}.corso"
        ));
			
			
        return $filters;

		
    }
}
