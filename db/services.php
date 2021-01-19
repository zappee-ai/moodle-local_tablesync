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
 * Define the function to start an on-demand table sync task.
 *
 * @package    local_tablesync
 * @copyright  2021 Zappee
 * @website    https://www.zappee.ai/
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$functions = array(
  'local_tablesync_start_sync' => array(
    'classname' => 'local_tablesync_external',
    'methodname' => 'start_sync',
    'classpath' => 'local/tablesync/externallib.php',
    'description' => 'Starts an on-demand table sync task.',
    'type' => 'read',
  )
);
