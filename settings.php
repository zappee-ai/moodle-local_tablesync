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
 * Settings for database connection and which tables to sync.
 *
 * @package    local_tablesync
 * @copyright  2021 Zappee
 * @website    https://www.zappee.ai/
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {

  // Setup connection test page
  $testurl = new moodle_url('/local/tablesync/test_connection.php', array('sesskey' => sesskey()));
  $test = new admin_externalpage(
    'local_tablesync_test_connection',
    'Test Destination DB Connection',
    $testurl,
    'moodle/site:config',
    true
  );
  $ADMIN->add('localplugins', $test);

  $settings = new admin_settingpage('local_tablesync', 'Zappee Table Sync Settings');
  $ADMIN->add('localplugins', $settings);

  if ($ADMIN->fulltree) {

    // Database connection settings
    $link = html_writer::link(
      $testurl,
      'Test Destination DB Connection',
      array('target' => '_blank')
    );
    $settings->add(new admin_setting_heading(
      'dbsettings',
      'Destination Database Connection',
      $link
    ));
    $drivers = \local_zappee\helper::get_drivers();
    $settings->add(new admin_setting_configselect(
      'local_tablesync/dbdriver',
      'Database Driver',
      '(Not all drivers have been tested)',
      '',
      $drivers
    ));
    $settings->add(new admin_setting_configtext(
      'local_tablesync/dbhost',
      'Database Host',
      '',
      ''
    ));
    $settings->add(new admin_setting_configtext(
      'local_tablesync/dbport',
      'Database Port',
      '',
      ''
    ));
    $settings->add(new admin_setting_configtext(
      'local_tablesync/dbuser',
      'Database Username',
      '',
      ''
    ));
    $settings->add(new admin_setting_configtext(
      'local_tablesync/dbpassword',
      'Database Password',
      '',
      ''
    ));
    $settings->add(new admin_setting_configtext(
      'local_tablesync/dbname',
      'Database Name',
      '',
      ''
    ));
    $settings->add(new admin_setting_configtext(
      'local_tablesync/tableprefix',
      'Destination Table Prefix',
      'Prepended before the mdl_ prefix',
      ''
    ));
    $settings->add(new admin_setting_configtext(
      'local_tablesync/buffersize',
      'Buffer Size ',
      'Number of log entries inserted in one batch database operation, which improves performance.',
      50,
      PARAM_INT
    ));

    // Tables to sync
    $settings->add(new admin_setting_heading(
      'tables',
      'Tables to Sync',
      'Destination tables must have identical structure to source tables. Specify the source tables in comma-separated lists below, without the mdl_ prefix. Example: grade_items,grade_grades'
    ));
    $settings->add(new admin_setting_configtext(
      'local_tablesync/timemodifiedtables',
      'timemodified Tables to Sync',
      'Any rows in these tables with timemodified later than the last sync will be inserted or updated.',
      ''
    ));
    $settings->add(new admin_setting_configtext(
      'local_tablesync/historytables',
      'history Tables to Sync ',
      'Any rows in these tables with id greater than the last-copied id will be inserted.',
      ''
    ));
  }
}
