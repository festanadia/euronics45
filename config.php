<?php  // Moodle configuration file

unset($CFG);
global $CFG;
$CFG = new stdClass();

$CFG->dbtype    = 'mysqli';
$CFG->dblibrary = 'native';
$CFG->dbhost    = 'euronics-el-prod-dbmdl-rds.chgswrdksami.eu-west-1.rds.amazonaws.com';
$CFG->dbname    = 'mdleuronics40';
$CFG->dbuser    = 'moodle';
$CFG->dbpass    = 'qT18FfQQ5usveU+6VEW0x6jj';
$CFG->prefix    = 'mdl_';
$CFG->dboptions = array (
  'dbpersist' => 0,
  'dbport' => 3306,
  'dbsocket' => '',
  'dbcollation' => 'utf8mb4_general_ci',
);

$CFG->wwwroot   = 'http://54.74.25.1/euronics453';
$CFG->dataroot  = '/data/mdldata40/moodledataeuronics40';
$CFG->admin     = 'admin';

$CFG->directorypermissions = 0777;

require_once(__DIR__ . '/lib/setup.php');

// There is no php closing tag in this file,
// it is intentional because it prevents trailing whitespace problems!
