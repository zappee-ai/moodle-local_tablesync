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
 * Scheduled task to sync database tables.
 *
 * @package    local_tablesync
 * @copyright  2021 Zappee
 * @website    https://www.zappee.ai/
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_tablesync\task;

use local_tablesync\mysqli_tablesync_moodle_database;

defined('MOODLE_INTERNAL') || die();

class sync_tables extends \core\task\scheduled_task
{
  public function get_name()
  {
    return 'Sync Tables';
  }

  public function execute()
  {
    global $CFG;
    global $DB;

    raise_memory_limit(MEMORY_HUGE);
    mtrace("sync_tables started");

    $destdb = \local_tablesync\util::get_destination_db();

    // Get source tables
    $timemodifiedtables = explode(',', get_config('local_tablesync', 'timemodifiedtables'));
    $historytables = explode(',', get_config('local_tablesync', 'historytables'));
    $customprefix = get_config('local_tablesync', 'tableprefix');

    // Get tables in destination
    $actualdesttables = $destdb->get_tables();

    foreach ($timemodifiedtables as $sourcetable) {
      $desttable = $customprefix . $CFG->prefix . trim($sourcetable);
      echo '* ' . $sourcetable . ' -> ' . $desttable . "\n";

      if (!in_array($desttable, $actualdesttables)) {
        echo "\tCannot find destination table " . $desttable . ' to sync ' . $sourcetable . '; skipping.' . "\n";
        continue;
      }

      $tablestarttime = microtime(true);

      // Get newest timemodified from destination table
      $lastmodified = $destdb->get_field($desttable, "max(timemodified)", []);
      if ($lastmodified === NULL) {
        echo "\tSyncing all rows\n";
        $lastmodified = 0;
      } else {
        echo "\tSyncing rows with timemodified >= $lastmodified\n";
      }

      // Get modified rows from source table
      $rs = $DB->get_recordset_sql("SELECT * FROM {" . $sourcetable . "} where timemodified >= ?", [$lastmodified]);

      // Sync modified rows
      // Custom mysqli subclass has replace_records method for better performance
      if ($destdb instanceof mysqli_tablesync_moodle_database) {
        $destdb->replace_records($desttable, $rs);
      } else {
        // For other drivers, use less-performant inserts
        foreach ($rs as $record) {
          echo "\t\t" . $record->id;
          $destdb->insert_record_raw($desttable, $record, false, true, true);
        }
      }
      $rs->close();

      echo "\tTook " . (microtime(true) - $tablestarttime) . " seconds\n";
    }

    foreach ($historytables as $historytable) {
      $desttable = $customprefix . $CFG->prefix . trim($historytable);
      echo "\t" . $historytable . ' -> ' . $desttable . "\n";
    }

    $destdb->dispose();

    mtrace("sync_tables finished");
  }
}
