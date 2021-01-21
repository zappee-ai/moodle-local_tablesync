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
 * Custom driver that extends mysqli to tailor data insertion methods
 * for the needs of efficiently syncing tables.
 *
 * @package    local_tablesync
 * @copyright  2021 Zappee
 * @website    https://www.zappee.ai/
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_tablesync;

use coding_exception;
use mysqli_native_moodle_database;

defined('MOODLE_INTERNAL') || die();

class mysqli_tablesync_moodle_database extends mysqli_native_moodle_database {

    /**
     * Replace multiple records into database as fast as possible.
     * (Modified from original mysqli driver’s insert_records method)
     * 
     * This will maintain the IDs in the rows, updating those rows if
     * present, or inserting them if not.
     *
     * Order of inserts is maintained, but the operation is not atomic,
     * use transactions if necessary.
     *
     * This method is intended for inserting a large number of small objects;
     * do not use for huge objects with text or binary fields.
     *
     * @param string $table  The database table to be replaced into
     * @param array|Traversable $dataobjects list of objects to be replaced, must be compatible with foreach
     * @return void does not return new record ids
     *
     * @throws coding_exception if data objects have different structure
     * @throws dml_exception A DML specific exception is thrown for any errors.
     */
    public function replace_records($table, $dataobjects) {
        // MySQL has a relatively small query length limit by default,
        // make sure 'max_allowed_packet' in my.cnf is high enough
        // if you change the following default...
        static $chunksize = null;
        if ($chunksize === null) {
            if (!empty($this->dboptions['bulkinsertsize'])) {
                $chunksize = (int)$this->dboptions['bulkinsertsize'];
            } else {
                if (PHP_INT_SIZE === 4) {
                    // Bad luck for Windows, we cannot do any maths with large numbers.
                    $chunksize = 5;
                } else {
                    $sql = "SHOW VARIABLES LIKE 'max_allowed_packet'";
                    $this->query_start($sql, null, SQL_QUERY_AUX);
                    $result = $this->mysqli->query($sql);
                    $this->query_end($result);
                    $size = 0;
                    if ($rec = $result->fetch_assoc()) {
                        $size = $rec['Value'];
                    }
                    $result->close();
                    // Hopefully 200kb per object are enough.
                    $chunksize = (int)($size / 200000);
                    if ($chunksize > 50) {
                        $chunksize = 50;
                    }
                }
            }
        }

        $columns = $this->get_columns($table, true);
        $fields = null;
        $count = 0;
        $chunk = array();
        foreach ($dataobjects as $dataobject) {
            if (!is_array($dataobject) and !is_object($dataobject)) {
                throw new coding_exception('replace_records() passed invalid record object');
            }
            $dataobject = (array)$dataobject;
            if ($fields === null) {
                $fields = array_keys($dataobject);
                $columns = array_intersect_key($columns, $dataobject);
                // Modified: no longer call unset($columns['id]])
            } else if ($fields !== array_keys($dataobject)) {
                throw new coding_exception('All dataobjects in replace_records() must have the same structure!');
            }

            $count++;
            $chunk[] = $dataobject;

            if ($count === $chunksize) {
                $this->replace_chunk($table, $chunk, $columns);
                $chunk = array();
                $count = 0;
            }
        }

        if ($count) {
            $this->replace_chunk($table, $chunk, $columns);
        }
    }

    /**
     * Replace records in chunks.
     * (Modified from original mysqli driver’s insert_chunk method)
     *
     * Note: can be used only from replace_records().
     *
     * @param string $table
     * @param array $chunk
     * @param database_column_info[] $columns
     */
    protected function replace_chunk($table, array $chunk, array $columns) {
        $fieldssql = '(' . implode(',', array_keys($columns)) . ')';

        $valuessql = '(' . implode(',', array_fill(0, count($columns), '?')) . ')';
        $valuessql = implode(',', array_fill(0, count($chunk), $valuessql));

        $params = array();
        foreach ($chunk as $dataobject) {
            foreach ($columns as $field => $column) {
                $params[] = $this->normalise_value($column, $dataobject[$field]);
            }
        }

        $fixedtable = $this->fix_table_name($table);
        // Modified: changed INSERT to REPLACE
        $sql = "REPLACE INTO $fixedtable $fieldssql VALUES $valuessql";

        list($sql, $params, $type) = $this->fix_sql_params($sql, $params);
        $rawsql = $this->emulate_bound_params($sql, $params);

        $this->query_start($sql, $params, SQL_QUERY_INSERT);
        $result = $this->mysqli->query($rawsql);
        $this->query_end($result);
    }
}
