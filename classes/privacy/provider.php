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
 * Data provider class.
 *
 * @package    local_tablesync
 * @copyright  2021 Zappee
 * @website    https://www.zappee.ai/
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_tablesync\privacy;

defined('MOODLE_INTERNAL') || die();

use context;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;
use local_tablesync\util;

class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider,
    \core_privacy\local\request\core_userlist_provider {
    /**
     * Returns metadata.
     *
     * @param collection $collection The initialised collection to add items to.
     * @return collection A listing of user data stored through this system.
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_external_location_link('synced_grade_grades', [
            'id' => 'privacy:metadata:grade_grades:id',
            'itemid' => 'privacy:metadata:grade_grades:itemid',
            'userid' => 'privacy:metadata:grade_grades:userid',
            'rawgrade' => 'privacy:metadata:grade_grades:rawgrade',
            'rawgrademax' => 'privacy:metadata:grade_grades:rawgrademax',
            'rawgrademin' => 'privacy:metadata:grade_grades:rawgrademin',
            'rawscaleid' => 'privacy:metadata:grade_grades:rawscaleid',
            'usermodified' => 'privacy:metadata:grade_grades:usermodified',
            'finalgrade' => 'privacy:metadata:grade_grades:finalgrade',
            'hidden' => 'privacy:metadata:grade_grades:hidden',
            'locked' => 'privacy:metadata:grade_grades:locked',
            'locktime' => 'privacy:metadata:grade_grades:locktime',
            'exported' => 'privacy:metadata:grade_grades:exported',
            'overridden' => 'privacy:metadata:grade_grades:overridden',
            'excluded' => 'privacy:metadata:grade_grades:excluded',
            'feedback' => 'privacy:metadata:grade_grades:feedback',
            'feedbackformat' => 'privacy:metadata:grade_grades:feedbackformat',
            'information' => 'privacy:metadata:grade_grades:information',
            'informationformat' => 'privacy:metadata:grade_grades:informationformat',
            'timecreated' => 'privacy:metadata:grade_grades:timecreated',
            'timemodified' => 'privacy:metadata:grade_grades:timemodified',
            'aggregationstatus' => 'privacy:metadata:grade_grades:aggregationstatus',
            'aggregationweight' => 'privacy:metadata:grade_grades:aggregationweight',
        ], 'privacy:metadata:grade_grades');

        $collection->add_external_location_link('synced_grade_grades_history', [
            'id' => 'privacy:metadata:grade_grades_history:id',
            'action' => 'privacy:metadata:grade_grades_history:action',
            'oldid' => 'privacy:metadata:grade_grades_history:oldid',
            'source' => 'privacy:metadata:grade_grades_history:source',
            'timemodified' => 'privacy:metadata:grade_grades_history:timemodified',
            'loggeduser' => 'privacy:metadata:grade_grades_history:loggeduser',
            'itemid' => 'privacy:metadata:grade_grades_history:itemid',
            'userid' => 'privacy:metadata:grade_grades_history:userid',
            'rawgrade' => 'privacy:metadata:grade_grades_history:rawgrade',
            'rawgrademax' => 'privacy:metadata:grade_grades_history:rawgrademax',
            'rawgrademin' => 'privacy:metadata:grade_grades_history:rawgrademin',
            'rawscaleid' => 'privacy:metadata:grade_grades_history:rawscaleid',
            'usermodified' => 'privacy:metadata:grade_grades_history:usermodified',
            'finalgrade' => 'privacy:metadata:grade_grades_history:finalgrade',
            'hidden' => 'privacy:metadata:grade_grades_history:hidden',
            'locked' => 'privacy:metadata:grade_grades_history:locked',
            'locktime' => 'privacy:metadata:grade_grades_history:locktime',
            'exported' => 'privacy:metadata:grade_grades_history:exported',
            'overridden' => 'privacy:metadata:grade_grades_history:overridden',
            'excluded' => 'privacy:metadata:grade_grades_history:excluded',
            'feedback' => 'privacy:metadata:grade_grades_history:feedback',
            'feedbackformat' => 'privacy:metadata:grade_grades_history:feedbackformat',
            'information' => 'privacy:metadata:grade_grades_history:information',
            'informationformat' => 'privacy:metadata:grade_grades_history:informationformat',
        ], 'privacy:metadata:grade_grades_history');

        return $collection;
    }

    /**
     * Get contexts that contain user information for the specified user.
     *
     * @param int $userid The user to find the contexts for.
     * @return contextlist $contextlist
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        global $DB;

        $contextlist = new \core_privacy\local\request\contextlist();
        $destdb = util::get_destination_db();

        if (!$destdb) {
            return $contextlist;
        }

        // Find grade items for which the user has grade grades (or history)
        $allitemids = [];
        $tables = static::get_all_table_names();
        foreach ($tables as $sourcetable) {
            $desttable = util::get_destination_table_name($sourcetable);
            $params = ['userid' => $userid];
            $itemids = $destdb->get_fieldset_select($desttable, 'DISTINCT itemid', "userid = :userid", $params);
            if (!empty($itemids)) {
                $allitemids = array_unique(array_merge($allitemids, $itemids));
            }
        }

        // Find courses for those grade items
        list($insql, $inparams) = $DB->get_in_or_equal($allitemids, SQL_PARAMS_NAMED);
        $courseids = $DB->get_fieldset_select("grade_items", "courseid", "id $insql", $inparams);

        // Find contexts from courses
        list($insql, $inparams) = $DB->get_in_or_equal($courseids, SQL_PARAMS_NAMED);
        $sql = "SELECT c.id FROM {context} c
                WHERE ( c.contextlevel = :contextlevel and c.instanceid $insql )";
        $params = array_merge($inparams, ['contextlevel' => CONTEXT_COURSE]);

        $contextlist->add_from_sql($sql, $params);
        return $contextlist;
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param   userlist    $userlist   The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {
        global $DB;
        $context = $userlist->get_context();
        if ($context->contextlevel == CONTEXT_COURSE) {
            // Get grade items for this course
            $params = ['courseid' => $context->instanceid];
            $itemids = $DB->get_fieldset_select("grade_items", "id", "courseid = :courseid", $params);

            $destdb = util::get_destination_db();
            if (!$destdb) {
                return;
            }
            list($insql, $inparams) = $destdb->get_in_or_equal($itemids, SQL_PARAMS_NAMED);

            // Get users that have grade grades (or history) related to those items
            $tables = static::get_all_table_names();
            foreach ($tables as $sourcetable) {
                $desttable = util::get_destination_table_name($sourcetable);
                $userids = $destdb->get_fieldset_select($desttable, 'DISTINCT userid', "itemid $insql", $inparams);
                if (!empty($userids)) {
                    $userlist->add_users($userids);
                }
            }
        }
    }

    /**
     * Export all user data for the specified user, in the specified contexts.
     *
     * @param   approved_contextlist    $contextlist    The approved contexts to export information for.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;
        $destdb = util::get_destination_db();
        if (!$destdb) {
            return;
        }
        $userid = $contextlist->get_user()->id;

        $path = get_string('privacy:path:tablesync', 'local_tablesync');
        $flush = function ($lastcontextid, $tablename, $data) use ($path) {
            $context = context::instance_by_id($lastcontextid);
            writer::with_context($context)->export_data([$path, $tablename], (object) ['sync' => $data]);
        };

        $tables = static::get_all_table_names();
        $contexts = $contextlist->get_contexts();

        foreach ($contexts as $context) {
            if ($context->contextlevel == CONTEXT_COURSE) {
                // Get grade item IDs for course
                $params = ['courseid' => $context->instanceid];
                $itemids = $DB->get_fieldset_select("grade_items", "id", "courseid = :courseid", $params);

                // Get all rows from grade_grades (and history) where userid and itemid match
                list($insql, $inparams) = $destdb->get_in_or_equal($itemids, SQL_PARAMS_QM);
                $params = array_merge([$userid], $inparams);
                foreach ($tables as $sourcetable) {
                    $desttable = util::get_destination_table_name($sourcetable);
                    // Export those
                    $recordset = $destdb->get_recordset_sql("SELECT * FROM " . $desttable . " where userid = ? and itemid $insql", $params);
                    $data = [];
                    foreach ($recordset as $record) {
                        $data[] = $record;
                    }
                    if (!empty($data)) {
                        $flush($context->id, $sourcetable, $data);
                    }
                    $recordset->close();
                }
            }
        }
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param   context                 $context   The specific context to delete data for.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;
        if ($context->contextlevel == CONTEXT_COURSE) {
            // Get grade items for this course
            $params = ['courseid' => $context->instanceid];
            $itemids = $DB->get_fieldset_select("grade_items", "id", "courseid = :courseid", $params);

            $destdb = util::get_destination_db();
            if (!$destdb) {
                return;
            }
            list($insql, $inparams) = $destdb->get_in_or_equal($itemids, SQL_PARAMS_NAMED);

            // Delete synced grade grades (and history) related to those items 
            $tables = static::get_all_table_names();
            foreach ($tables as $sourcetable) {
                $desttable = util::get_destination_table_name($sourcetable);
                $destdb->delete_records_select($desttable, "itemid $insql", $inparams);
            }
        }
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param   approved_contextlist    $contextlist    The approved contexts and user information to delete information for.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;
        $destdb = util::get_destination_db();
        if (!$destdb) {
            return;
        }

        $tables = static::get_all_table_names();
        $contexts = $contextlist->get_contexts();

        foreach ($contexts as $context) {
            if ($context->contextlevel == CONTEXT_COURSE) {
                // Get grade item IDs for course
                $params = ['courseid' => $context->instanceid];
                $itemids = $DB->get_fieldset_select("grade_items", "id", "courseid = :courseid", $params);

                // Get all rows from grade_grades (and history) where userid and itemid match
                list($insql, $inparams) = $destdb->get_in_or_equal($itemids, SQL_PARAMS_QM);
                $params = array_merge([$contextlist->get_user()->id], $inparams);
                foreach ($tables as $sourcetable) {
                    $desttable = util::get_destination_table_name($sourcetable);
                    // Delete those
                    $destdb->delete_records_select($desttable, "userid = ? and itemid $insql", $params);
                }
            }
        }
    }

    /**
     * Delete multiple users’ data within a single context.
     *
     * @param   approved_userlist       $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;
        $destdb = util::get_destination_db();
        if (!$destdb) {
            return;
        }

        $tables = static::get_all_table_names();
        foreach ($tables as $sourcetable) {
            $desttable = util::get_destination_table_name($sourcetable);
            list($insql, $inparams) = $destdb->get_in_or_equal($userlist->get_userids(), SQL_PARAMS_NAMED);
            // TODO figure out how to deal with specific contexts in these particular tables
            $destdb->delete_records_select($desttable, "userid $insql", $inparams);
        }

        $tables = static::get_all_table_names();
        $context = $userlist->get_context();

        if ($context->contextlevel == CONTEXT_COURSE) {
            // Get grade item IDs for course
            $params = ['courseid' => $context->instanceid];
            $itemids = $DB->get_fieldset_select("grade_items", "id", "courseid = :courseid", $params);

            // Get all rows from grade_grades (and history) where userid and itemid match
            list($iteminsql, $iteminparams) = $destdb->get_in_or_equal($itemids, SQL_PARAMS_QM);
            list($userinsql, $userinparams) = $destdb->get_in_or_equal($userlist->get_userids(), SQL_PARAMS_QM);
            $params = array_merge($iteminparams, $userinparams);
            foreach ($tables as $sourcetable) {
                $desttable = util::get_destination_table_name($sourcetable);
                // Delete those
                $destdb->delete_records_select($desttable, "itemid $iteminsql and userid $userinsql", $params);
            }
        }
    }

    /**
     * Returns the names of all (history and timemodified) tables that are synced.
     * Currently only grade_grades and grade_grades_history contain personal data and are synced
     * @return string[]
     */
    private static function get_all_table_names() {
        return ['grade_grades', 'grade_grades_history'];
    }
}
