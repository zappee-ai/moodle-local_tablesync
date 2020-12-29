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

class helper
{
  /**
   * Returns list of fully working database drivers present in system.
   * @return array
   */
  public static function get_drivers()
  {
    return array(
      ''               => get_string('choosedots'),
      'native/mysqli'  => \moodle_database::get_driver_instance('mysqli', 'native')->get_name(),
      'native/mariadb' => \moodle_database::get_driver_instance('mariadb', 'native')->get_name(),
      'native/pgsql'   => \moodle_database::get_driver_instance('pgsql', 'native')->get_name(),
      'native/oci'     => \moodle_database::get_driver_instance('oci', 'native')->get_name(),
      'native/sqlsrv'  => \moodle_database::get_driver_instance('sqlsrv', 'native')->get_name()
    );
  }
}
