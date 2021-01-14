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
 * Define the external function to start an on-demand table sync task.
 *
 * @package    local_tablesync
 * @copyright  2021 Zappee
 * @website    https://www.zappee.ai/
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("$CFG->libdir/externallib.php");

use local_tablesync\task\sync_tables_adhoc;

class local_tablesync_external extends external_api
{

  /**
   * Returns description of method parameters
   * @return external_function_parameters
   */
  public static function start_sync_parameters()
  {
    // No input parameters for this function
    return new external_function_parameters(array());
  }

  /**
   * Returns description of method result value
   * @return external_description
   */
  public static function start_sync_returns()
  {
    return
      new external_single_structure(
        array(
          'message' => new external_value(PARAM_TEXT, 'Message describing result')
        )
      );
  }

  /**
   * Kicks off an adhoc task to sync tables.
   * @return string message
   */
  public static function start_sync()
  {
    self::validate_parameters(self::start_sync_parameters(), array());

    $context = get_context_instance(CONTEXT_SYSTEM);
    self::validate_context($context);

    $task = new sync_tables_adhoc();
    \core\task\manager::queue_adhoc_task($task);

    return array('message' => 'Enqueued adhoc table sync task');
  }
}
