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
 * Page that tests the destination database connection and checks for tables.
 *
 * @package    local_tablesync
 * @copyright  2021 Zappee
 * @website    https://www.zappee.ai/
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_tablesync\util;

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

$olddebug = $CFG->debug;
$olddisplay = ini_get('display_errors');
ini_set('display_errors', '1');
$CFG->debug = DEBUG_DEVELOPER;
error_reporting($CFG->debug);

// Get database connection (will die here if connection fails)
$db = util::get_destination_db();
if (!$db) {
    echo $OUTPUT->notification('Connection not correctly configured.', 'notifyproblem');
    die();
}
echo $OUTPUT->notification('Connection made.', 'notifysuccess');


$enabled = get_config('local_tablesync', 'enabled') === 'yes';
if (!$enabled) {
    echo $OUTPUT->notification('Table Sync is not enabled; no data will sync until it is enabled.', 'notifywarning');
}

// List all tables in database
$tables = $db->get_tables();
if (empty($tables)) {
    echo $OUTPUT->notification('Can not read tables from destination database. Ensure that the database user has permission to list tables.', 'notifyproblem');
} else {
    $tables = array_keys((array)$tables);
    echo 'Destination database contains the following tables. Please ensure their schemas are identical to the tables in Moodle.<br />' . implode('<br /> ', $tables) . '<hr />';
}

// Check for each table needed
$timemodifiedtables = explode(',', get_config('local_tablesync', 'timemodifiedtables'));
$historytables = explode(',', get_config('local_tablesync', 'historytables'));
$sourcetables = array_merge($timemodifiedtables, $historytables);

foreach ($sourcetables as $sourcetable) {
    $destname = util::get_destination_table_name($sourcetable);
    if (in_array($destname, $tables)) {
        echo $OUTPUT->notification('Table ' . $sourcetable . ' will be synced to ' . $destname, 'notifysuccess');
    } else {
        echo $OUTPUT->notification('Cannot find destination table ' . $destname . ' to sync ' . $sourcetable, 'notifyproblem');
    }
}

$db->dispose();

$CFG->debug = $olddebug;
ini_set('display_errors', $olddisplay);
error_reporting($CFG->debug);
ob_end_flush();
echo $OUTPUT->footer();
