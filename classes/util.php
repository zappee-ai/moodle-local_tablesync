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
 * Utilities class used locally.
 *
 * @package    local_tablesync
 * @copyright  2021 Zappee
 * @website    https://www.zappee.ai/
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_tablesync;

defined('MOODLE_INTERNAL') || die();

class util {
    /**
     * Returns list of database drivers present in system.
     * @return array
     */
    public static function get_drivers() {
        return array(
            ''               => get_string('choosedots'),
            'tablesync/mysqli' => 'Table Sync Optimized MySQL/MariaDB',
            'native/mysqli'  => \moodle_database::get_driver_instance('mysqli', 'native')->get_name(),
            'native/mariadb' => \moodle_database::get_driver_instance('mariadb', 'native')->get_name(),
            'native/pgsql'   => \moodle_database::get_driver_instance('pgsql', 'native')->get_name(),
            'native/oci'     => \moodle_database::get_driver_instance('oci', 'native')->get_name(),
            'native/sqlsrv'  => \moodle_database::get_driver_instance('sqlsrv', 'native')->get_name()
        );
    }

    /**
     * Returns the names of history tables to sync.
     * @return string[]
     */
    public static function get_history_table_names() {
        // Once plugin is configurable, update this
        return explode(',', 'grade_grades_history,grade_items_history');
    }

    /**
     * Returns the names of timemodified tables to sync.
     * @return string[]
     */
    public static function get_timemodified_table_names() {
        // Once plugin is configurable, update this
        return explode(',', 'grade_items,grade_grades');
    }

    /**
     * Returns the computed destination table name, which includes
     * the custom prefix and Moodle-configured prefix.
     * @return string
     */
    public static function get_destination_table_name($sourcetable) {
        global $CFG;
        $customprefix = get_config('local_tablesync', 'tableprefix');
        return $customprefix . $CFG->prefix . trim($sourcetable);
    }

    /**
     * Returns a connection to the destination database.
     * @return moodle_database driver object or null if error
     */
    public static function get_destination_db() {
        $dbdriver = get_config('local_tablesync', 'dbdriver');

        if (empty($dbdriver) || empty(get_config('local_tablesync', 'dbhost'))) {
            return false;
        }

        // Handle custom driver construction separately
        if ($dbdriver === "tablesync/mysqli") {
            $db = new \local_tablesync\mysqli_tablesync_moodle_database(true);
        } else {
            list($dblibrary, $dbtype) = explode('/', $dbdriver);
            $db = \moodle_database::get_driver_instance($dbtype, $dblibrary, true);
        }

        $dboptions = array();
        $dboptions['dbport'] = get_config('local_tablesync', 'dbport');
        $dboptions['dbcollation'] = get_config('local_tablesync', 'dbcollation');

        // Connect to destination database
        try {
            $db->connect(
                get_config('local_tablesync', 'dbhost'),
                get_config('local_tablesync', 'dbuser'),
                get_config('local_tablesync', 'dbpassword'),
                get_config('local_tablesync', 'dbname'),
                false,
                $dboptions
            );
            return $db;
        } catch (\moodle_exception $e) {
            die('Cannot connect to the destination tablesync database. ' . $e->getMessage());
        }
    }
}
