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
class local_report_catalogo extends base {

    /**
     * Database tables that this entity uses and their default aliases
     *
     * @return array
     */
    protected function get_default_table_aliases(): array {
        return [
            'local_report_catalogo' => 'eur',
        ];
    }

    /**
     * The default title for this entity
     *
     * @return lang_string
     */
    protected function get_default_entity_title(): lang_string {
        return new lang_string('local_report_catalogo', 'local_ard_report');
    }

    /**
     * Initialise the entity
     *
     * @return base
     */
    public function initialise(): base {
		
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

        $reportalias = $this->get_table_alias('local_report_catalogo');

        // Codice
        $columns[] = (new column(
            'codice',
            new lang_string('codice', 'local_ard_report'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_field("{$reportalias}.codice")
            ->add_callback(static function(?string $name, stdClass $reportalias): string { 
                return format_string($reportalias->codice);
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
			
        // visibile
        $columns[] = (new column(
            'visibile',
            new lang_string('visibile', 'local_ard_report'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_field("{$reportalias}.visibile")
            ->add_callback(static function(?string $name, stdClass $reportalias): string { 
                return format_string($reportalias->visibile);
            })			
            ->set_is_sortable(true);
			
        // format
        $columns[] = (new column(
            'format',
            new lang_string('format', 'local_ard_report'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_field("{$reportalias}.format")
            ->add_callback(static function(?string $name, stdClass $reportalias): string { 
                return format_string($reportalias->format);
            })			
            ->set_is_sortable(true);

        // datacreazione
        $columns[] = (new column(
            'datacreazione',
            new lang_string('datacreazione', 'local_ard_report'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_field("{$reportalias}.datacreazione")
            ->add_callback(static function(?string $name, stdClass $reportalias): string { 
                return format_string($reportalias->datacreazione);
            })			
            ->set_is_sortable(true);			

        // datadisattivazione
        $columns[] = (new column(
            'datadisattivazione',
            new lang_string('datadisattivazione', 'local_ard_report'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_field("{$reportalias}.datadisattivazione")
            ->add_callback(static function(?string $name, stdClass $reportalias): string { 
                return format_string($reportalias->datadisattivazione);
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
        $entityalias = $this->get_table_alias('local_report_catalogo');

        // Corso
        $filters[] = (new filter(
            text::class,
            'corso',
            new lang_string('corso', 'local_ard_report'),
            $this->get_entity_name(),
            "{$entityalias}.corso"
        ));
			
        return $filters;
		
    }
}
