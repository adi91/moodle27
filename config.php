<?php  // Moodle configuration file

unset($CFG);
global $CFG;
$CFG = new stdClass();

$CFG->dbtype    = 'mysqli';
$CFG->dblibrary = 'native';
$CFG->dbhost    = 'localhost'; // path of server host
$CFG->dbname    = 'moodle27';
$CFG->dbuser    = 'root';
$CFG->dbpass    = '';
$CFG->prefix    = 'mdl_';
$CFG->dboptions = array (
  'dbpersist' => 0,
  'dbport' => '',
  'dbsocket' => '',
);

$CFG->wwwroot   = 'http://localhost/moodle27';
$CFG->dataroot  = 'C:\moodledata\moodledata27';
$CFG->admin     = 'admin';

$CFG->directorypermissions = 0777;

require_once(dirname(__FILE__) . '/lib/setup.php');

$CFG->allowedroles = array('manager', 'editingteacher', 'teacher', 'student', 'user');


// There is no php closing tag in this file,
// it is intentional because it prevents trailing whitespace problems!
