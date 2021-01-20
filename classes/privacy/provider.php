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
use core_privacy\local\metadata\collection;
use core_privacy\local\request\contextlist;

if (interface_exists('\core_privacy\local\request\core_userlist_provider')) {
    interface my_userlist_provider extends \core_privacy\local\request\core_userlist_provider
    {
    }
} else {
    interface my_userlist_provider
    {
    };
}

class provider implements
    \core_privacy\local\metadata\provider,
    // TODO
    // \tool_log\local\privacy\logstore_provider,
    // \tool_log\local\privacy\logstore_userlist_provider
    \core_privacy\local\request\plugin\provider,
    my_userlist_provider
{

    // TODO
    // use \tool_log\local\privacy\moodle_database_export_and_delete;

    /**
     * Returns metadata.
     *
     * @param collection $collection The initialised collection to add items to.
     * @return collection A listing of user data stored through this system.
     */
    public static function get_metadata(collection $collection): collection
    {
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
    public static function get_contexts_for_userid(int $userid)
    {
        // TODO fetch distinct context IDs from all synced tables
        $contextlist = new contextlist();
        list($db, $table) = static::get_database_and_table();
        if (!$db || !$table) {
            return;
        }

        $sql = 'userid = :userid1 OR relateduserid = :userid2 OR realuserid = :userid3';
        $params = ['userid1' => $userid, 'userid2' => $userid, 'userid3' => $userid];
        $contextids = $db->get_fieldset_select($table, 'DISTINCT contextid', $sql, $params);
        if (empty($contextids)) {
            return;
        }

        $sql = implode(' UNION ', array_map(function ($id) use ($db) {
            return 'SELECT ' . $id . $db->sql_null_from_clause();
        }, $contextids));
        $contextlist->add_from_sql($sql, []);
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param   userlist    $userlist   The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist)
    {
        // TODO fetch distinct user IDs from all synced tables
        list($db, $table) = static::get_database_and_table();
        if (!$db || !$table) {
            return;
        }

        $userids = [];
        $records = $db->get_records(
            $table,
            ['contextid' => $userlist->get_context()->id],
            '',
            'id, userid, relateduserid, realuserid'
        );
        if (empty($records)) {
            return;
        }

        foreach ($records as $record) {
            $userids[] = $record->userid;
            if (!empty($record->relateduserid)) {
                $userids[] = $record->relateduserid;
            }
            if (!empty($record->realuserid)) {
                $userids[] = $record->realuserid;
            }
        }
        $userids = array_unique($userids);
        $userlist->add_users($userids);
    }

    /**
     * Export all user data for the specified user, in the specified contexts.
     *
     * @param   approved_contextlist    $contextlist    The approved contexts to export information for.
     */
    public static function export_user_data(approved_contextlist $contextlist)
    {
        // TODO
        list($db, $table) = static::get_database_and_table();
        if (!$db || !$table) {
            return;
        }

        $userid = $contextlist->get_user()->id;
        list($insql, $inparams) = $db->get_in_or_equal($contextlist->get_contextids(), SQL_PARAMS_NAMED);

        $sql = "(userid = :userid1 OR relateduserid = :userid2 OR realuserid = :userid3) AND contextid $insql";
        $params = array_merge($inparams, [
            'userid1' => $userid,
            'userid2' => $userid,
            'userid3' => $userid,
        ]);

        $path = static::get_export_subcontext();
        $flush = function($lastcontextid, $data) use ($path) {
            $context = context::instance_by_id($lastcontextid);
            writer::with_context($context)->export_data($path, (object) ['logs' => $data]);
        };

        $lastcontextid = null;
        $data = [];
        $recordset = $db->get_recordset_select($table, $sql, $params, 'contextid, timecreated, id');
        foreach ($recordset as $record) {
            if ($lastcontextid && $lastcontextid != $record->contextid) {
                $flush($lastcontextid, $data);
                $data = [];
            }
            $data[] = helper::transform_standard_log_record_for_userid($record, $userid);
            $lastcontextid = $record->contextid;
        }
        if ($lastcontextid) {
            $flush($lastcontextid, $data);
        }
        $recordset->close();
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param   context                 $context   The specific context to delete data for.
     */
    public static function delete_data_for_all_users_in_context(\context $context)
    {
        // TODO
        list($db, $table) = static::get_database_and_table();
        if (!$db || !$table) {
            return;
        }
        $db->delete_records($table, ['contextid' => $context->id]);
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param   approved_contextlist    $contextlist    The approved contexts and user information to delete information for.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist)
    {
        // TODO
        list($db, $table) = static::get_database_and_table();
        if (!$db || !$table) {
            return;
        }
        list($insql, $inparams) = $db->get_in_or_equal($contextlist->get_contextids(), SQL_PARAMS_NAMED);
        $params = array_merge($inparams, ['userid' => $contextlist->get_user()->id]);
        $db->delete_records_select($table, "userid = :userid AND contextid $insql", $params);
    }


    /**
     * Delete multiple users within a single context.
     *
     * @param   approved_userlist       $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist)
    {
        // TODO delete user data in all tables
        list($db, $table) = static::get_database_and_table();
        if (!$db || !$table) {
            return;
        }
        list($insql, $inparams) = $db->get_in_or_equal($userlist->get_userids(), SQL_PARAMS_NAMED);
        $params = array_merge($inparams, ['contextid' => $userlist->get_context()->id]);
        $db->delete_records_select($table, "contextid = :contextid AND userid $insql", $params);
    }

}
