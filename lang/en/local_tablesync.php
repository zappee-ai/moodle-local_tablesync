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
 * Language strings for tablesync plugin.
 *
 * @package    local_tablesync
 * @copyright  2021 Zappee
 * @website    https://www.zappee.ai/
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Table Sync';
$string['pluginname_desc'] = 'This plugin syncs Moodle tables to an external database.';
$string['privacy:metadata:grade_grades'] = 'Synced grade grades entries';
$string['privacy:metadata:grade_grades:id'] = 'The id of the grade grades entry';
$string['privacy:metadata:grade_grades:itemid'] = 'The item id for the relevant item';
$string['privacy:metadata:grade_grades:userid'] = 'The user id attached to the grade';
$string['privacy:metadata:grade_grades:rawgrade'] = 'The raw grade value';
$string['privacy:metadata:grade_grades:rawgrademax'] = 'The raw grade max value';
$string['privacy:metadata:grade_grades:rawgrademin'] = 'The raw grade min value';
$string['privacy:metadata:grade_grades:rawscaleid'] = 'The raw scale id for the grade';
$string['privacy:metadata:grade_grades:usermodified'] = 'The user id for the user which modified the grade';
$string['privacy:metadata:grade_grades:finalgrade'] = 'The final grade value';
$string['privacy:metadata:grade_grades:hidden'] = '0 is hidden, 1 is hide always, greater than 1 is a date to hide until';
$string['privacy:metadata:grade_grades:locked'] = '0 is not locked, greater than 0 is the date when the grade was locked';
$string['privacy:metadata:grade_grades:locktime'] = '0 is never, greater than 0 is date to lock the final grade';
$string['privacy:metadata:grade_grades:exported'] = '0 is not exported, greater than 0 is date the grade was last exported';
$string['privacy:metadata:grade_grades:overridden'] = '0 is not overridden, greater than 0 is the last overridden date';
$string['privacy:metadata:grade_grades:excluded'] = 'Grade excluded from aggregation, greater than 0 is last exported date';
$string['privacy:metadata:grade_grades:feedback'] = 'Feedback provided on the grade';
$string['privacy:metadata:grade_grades:feedbackformat'] = 'The format for the provided feedback';
$string['privacy:metadata:grade_grades:information'] = 'Additional information for the grade';
$string['privacy:metadata:grade_grades:informationformat'] = 'The format for the additional grade information';
$string['privacy:metadata:grade_grades:timecreated'] = 'The time the grade was given for the first time';
$string['privacy:metadata:grade_grades:timemodified'] = 'The time the grade was modified for the last time';
$string['privacy:metadata:grade_grades:aggregationstatus'] = 'The aggregation status for the grade';
$string['privacy:metadata:grade_grades:aggregationweight'] = 'The aggregation weight for the grade';
$string['privacy:metadata:grade_grades_history'] = 'Synced grade grades history entries';
$string['privacy:metadata:grade_grades_history:id'] = 'The id of the grade history entry';
$string['privacy:metadata:grade_grades_history:action'] = 'The action taken to modify a grade';
$string['privacy:metadata:grade_grades_history:oldid'] = 'The primary key in the non-history tablee associated with the grade being modified';
$string['privacy:metadata:grade_grades_history:source'] = 'The module fromr which the source originated';
$string['privacy:metadata:grade_grades_history:timemodified'] = 'The time at which the grade was modified';
$string['privacy:metadata:grade_grades_history:loggeduser'] = 'The logged user that modified the grade';
$string['privacy:metadata:grade_grades_history:itemid'] = 'The item id associated with the grade change';
$string['privacy:metadata:grade_grades_history:userid'] = 'The user id associated with the grade change';
$string['privacy:metadata:grade_grades_history:rawgrade'] = 'The final raw grade after the change';
$string['privacy:metadata:grade_grades_history:rawgrademax'] = 'The final raw grade max after the change';
$string['privacy:metadata:grade_grades_history:rawgrademin'] = 'The final raw grade min after the change';
$string['privacy:metadata:grade_grades_history:rawscaleid'] = 'The final raw scale id after the change';
$string['privacy:metadata:grade_grades_history:usermodified'] = 'The user id which modified the grade';
$string['privacy:metadata:grade_grades_history:finalgrade'] = 'The final grade after the change';
$string['privacy:metadata:grade_grades_history:hidden'] = '0 is hidden, 1 is hide always, greater than 1 is a date to hide until';
$string['privacy:metadata:grade_grades_history:locked'] = '0 is not locked, greater than 0 is the date when the grade was locked';
$string['privacy:metadata:grade_grades_history:locktime'] = '0 is never, greater than 0 is date to lock the final grade';
$string['privacy:metadata:grade_grades_history:exported'] = '0 is not exported, greater than 0 is date the grade was last exported';
$string['privacy:metadata:grade_grades_history:overridden'] = '0 is not overridden, greater than 0 is the last overridden date';
$string['privacy:metadata:grade_grades_history:excluded'] = 'Grade excluded from aggregation, greater than 0 is last exported date';
$string['privacy:metadata:grade_grades_history:feedback'] = 'The feedback provided for the grade';
$string['privacy:metadata:grade_grades_history:feedbackformat'] = 'The format of the provided feedback';
$string['privacy:metadata:grade_grades_history:information'] = 'The information associated with the grade';
$string['privacy:metadata:grade_grades_history:informationformat'] = 'The format of the associated information';
$string['privacy:metadata:logstore_standard_log'] = 'Synced log store entries';
$string['privacy:metadata:logstore_standard_log:id'] = 'The ID of the event';
$string['privacy:metadata:logstore_standard_log:eventname'] = 'Automatically computed by copying class name';
$string['privacy:metadata:logstore_standard_log:component'] = 'Component declaring the event, automatically computed from class name';
$string['privacy:metadata:logstore_standard_log:action'] = 'Can be automatically computed from class name';
$string['privacy:metadata:logstore_standard_log:target'] = 'Target on which the action is taken, can be automatically computed from class name';
$string['privacy:metadata:logstore_standard_log:objecttable'] = 'Database table name which represents the event object to the best';
$string['privacy:metadata:logstore_standard_log:objectid'] = 'ID of the object record from objecttable';
$string['privacy:metadata:logstore_standard_log:crud'] = 'One of the [crud] letters, create read update or delete';
$string['privacy:metadata:logstore_standard_log:edulevel'] = 'Level of educational value of the event';
$string['privacy:metadata:logstore_standard_log:contextid'] = 'The context Id for the event';
$string['privacy:metadata:logstore_standard_log:contextlevel'] = 'Tells you if this was a course, activity, course category, etc.';
$string['privacy:metadata:logstore_standard_log:contextinstanceid'] = 'Based on context level this may be course id, course module id, course category, etc.';
$string['privacy:metadata:logstore_standard_log:userid'] = 'User ID, or 0 when not logged in, or -1 when other';
$string['privacy:metadata:logstore_standard_log:courseid'] = 'This is used only for contexts at and below course level (can be used to filter events by course)';
$string['privacy:metadata:logstore_standard_log:relateduserid'] = 'Is this action related to some user? This could be used for some personal timeline view';
$string['privacy:metadata:logstore_standard_log:anonymous'] = 'Is this action anonymous?';
$string['privacy:metadata:logstore_standard_log:other'] = 'Any other fields needed for event description - scalars or arrays';
$string['privacy:metadata:logstore_standard_log:timecreated'] = 'The time the event was created';
$string['privacy:metadata:logstore_standard_log:origin'] = 'The origin for the event';
$string['privacy:metadata:logstore_standard_log:ip'] = 'The ip address of the user';
$string['privacy:metadata:logstore_standard_log:realuser'] = 'The user masquerading as another user, if there is one';

$string['privacy:path:tablesync'] = 'Table Sync';
