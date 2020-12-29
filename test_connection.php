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
 * Page that tests the destination database connection.
 *
 * @package    local_tablesync
 * @copyright  2021 Zappee
 * @website    https://www.zappee.ai/
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/lib/adminlib.php');

require_sesskey();

navigation_node::override_active_url(new moodle_url('/admin/settings.php', array('section' => 'local_tablesync_test_connection')));
admin_externalpage_setup('local_tablesync_test_connection');

echo $OUTPUT->header();
echo $OUTPUT->heading('Test Destination DB Connection');

raise_memory_limit(MEMORY_HUGE);
$dbname = get_config('local_tablesync', 'dbname');
if (empty($dbname)) {
  echo $OUTPUT->notification('Destination database name not specified.', 'notifyproblem');
  die();
}

$dbdriver = get_config('local_tablesync', 'dbdriver');
list($dblibrary, $dbtype) = explode('/', $dbdriver);
if (!$db = \moodle_database::get_driver_instance($dbtype, $dblibrary, true)) {
  echo $OUTPUT->notification("Unknown driver $dblibrary/$dbtype", "notifyproblem");
  die();
}

$olddebug = $CFG->debug;
$olddisplay = ini_get('display_errors');
ini_set('display_errors', '1');
$CFG->debug = DEBUG_DEVELOPER;
error_reporting($CFG->debug);

$dboptions = array();
$dboptions['dbport'] = get_config('local_tablesync', 'dbport');

try {
  $db->connect(
    get_config('local_tablesync', 'dbhost'),
    get_config('local_tablesync', 'dbuser'),
    get_config('local_tablesync', 'dbpassword'),
    get_config('local_tablesync', 'dbname'),
    false,
    $dboptions
  );
} catch (\moodle_exception $e) {
  echo $OUTPUT->notification('Cannot connect to the database.', 'notifyproblem');
  $CFG->debug = $olddebug;
  ini_set('display_errors', $olddisplay);
  error_reporting($CFG->debug);
  ob_end_flush();
  echo $OUTPUT->footer();
  die();
}

echo $OUTPUT->notification('Connection made.', 'notifysuccess');
$tables = $db->get_tables();
if (empty($cols)) {
  echo $OUTPUT->notification('Can not read external tables.', 'notifyproblem');
} else {
  $tables = array_keys((array)$tables);
  echo $OUTPUT->notification('Destination database contains following tables:<br />' . implode(', ', $tables), 'notifysuccess');
}
// TODO check for each table needed
// if (!in_array($dbtable, $tables)) {
//   echo $OUTPUT->notification('Cannot find the specified table ' . $dbtable, 'notifyproblem');
//   $CFG->debug = $olddebug;
//   ini_set('display_errors', $olddisplay);
//   error_reporting($CFG->debug);
//   ob_end_flush();
//   echo $OUTPUT->footer();
//   die();
// }
// echo $OUTPUT->notification('Table ' . $dbtable . ' found.', 'notifysuccess');


$db->dispose();

$CFG->debug = $olddebug;
ini_set('display_errors', $olddisplay);
error_reporting($CFG->debug);
ob_end_flush();
echo $OUTPUT->footer();
