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
 * Functions to sync database tables.
 *
 * @package    local_tablesync
 * @copyright  2021 Zappee
 * @website    https://www.zappee.ai/
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_tablesync;

use local_tablesync\mysqli_tablesync_moodle_database;
use local_tablesync\util;

defined('MOODLE_INTERNAL') || die();

class sync {
    /**
     * Replace records into a destination table.
     * @return void
     */
    private static function replace_records($destdb, $desttable, $recordset) {
        // Custom mysqli subclass has replace_records method for better performance
        if ($destdb instanceof mysqli_tablesync_moodle_database) {
            $destdb->replace_records($desttable, $recordset);
        } else {
            // For other drivers, use less-performant inserts
            foreach ($recordset as $record) {
                $destdb->insert_record_raw($desttable, $record, false, true, true);
            }
        }
        $recordset->close();
    }

    /**
     * Sync a given source table to a destination table in destdb.
     */
    private static function sync_table($destdb, $sourcetable, $actualdesttables, $synctype, $syncdeletions = false) {
        global $DB;

        $sourcetable = trim($sourcetable);

        $desttable = util::get_destination_table_name($sourcetable);
        echo '* ' . $sourcetable . ' -> ' . $desttable . "\n";

        if (!in_array($desttable, $actualdesttables)) {
            echo "\tCannot find destination table " . $desttable . " to sync " . $sourcetable . "; skipping.\n";
            return;
        }

        $tablestarttime = microtime(true);

        if ($synctype === "timemodified") {
            // Get newest timemodified from destination table
            $lastmodified = $destdb->get_field($desttable, "max(timemodified)", []);
            if ($lastmodified === NULL) {
                echo "\tSyncing all rows\n";
                $lastmodified = 0;
            } else {
                echo "\tSyncing rows with timemodified >= $lastmodified\n";
            }

            // Get modified rows from source table
            $recordset = $DB->get_recordset_sql("SELECT * FROM {" . $sourcetable . "} where timemodified >= ?", [$lastmodified]);
        } else if ($synctype == "history") {
            // Get greatest id from destination table
            $lastid = $destdb->get_field($desttable, "max(id)", []);
            if ($lastid === NULL) {
                echo "\tSyncing all rows\n";
                $lastid = -1;
            } else {
                echo "\tSyncing rows with id > $lastid\n";
            }

            // Get modified rows from source table
            $recordset = $DB->get_recordset_sql("SELECT * FROM {" . $sourcetable . "} where id > ?", [$lastid]);
        } else {
            echo "\tUnknown synctype $synctype; skipping.\n";
        }

        // Sync modified rows
        self::replace_records($destdb, $desttable, $recordset);

        // Handle deletions for timemodified tables, if requested
        // (Rows will never be deleted from destination history tables)
        if ($synctype === "timemodified" && $syncdeletions) {
            echo "\tSyncing deletions\n";

            $sourceids = $DB->get_fieldset_select($sourcetable, "id", "");
            $destids = $destdb->get_fieldset_select($desttable, "id", "");
            echo "\tCounts before deletion syncing: source: " . count($sourceids) . ", dest: " . count($destids) . "\n";

            // Delete from destination any rows present in destination but not in source
            $deletedids = array_diff($destids, $sourceids);

            if (count($deletedids) > 0) {
                $deletedlist = implode(',', $deletedids);
                $destdb->delete_records_select($desttable, "id in ($deletedlist)");

                echo "\t" . count($deletedids) . " rows deleted from destination\n";
                echo "\t" . implode(',', $deletedids) . "\n";
            } else {
                echo "\tNo rows to delete.\n";
            }
        }

        echo "\tTook " . (microtime(true) - $tablestarttime) . " seconds\n";
    }

    /**
     * Sync all the configured tables (both timemodified and history).
     * This should be called from a scheduled or adhoc task.
     */
    public static function sync_tables() {
        raise_memory_limit(MEMORY_HUGE);
        mtrace("sync_tables started");

        $destdb = util::get_destination_db();

        // Get tables in destination
        $actualdesttables = $destdb->get_tables();

        // Sync timemodified tables
        $timemodifiedtables = util::get_timemodified_table_names();
        $syncdeletions = get_config('local_tablesync', 'syncdeletions') === 'yes';
        foreach ($timemodifiedtables as $sourcetable) {
            self::sync_table($destdb, $sourcetable, $actualdesttables, "timemodified", $syncdeletions);
        }

        // Sync history tables
        $historytables = util::get_history_table_names();
        foreach ($historytables as $sourcetable) {
            self::sync_table($destdb, $sourcetable, $actualdesttables, "history");
        }

        $destdb->dispose();

        mtrace("sync_tables finished");
    }
}
