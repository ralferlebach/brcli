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
 * Backup courses of a category via CLI.
 *
 * @package    tool_brcli
 * @copyright  2019 Paulo Júnior
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/clilib.php');
require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');

/**
 * Safely set a backup plan setting if it exists.
 *
 * Moodle versions and backup modes may expose different settings,
 * so we set only when present.
 *
 * @param backup_plan $plan        The backup plan instance.
 * @param string      $settingname The name of the setting to set.
 * @param mixed       $value       The value to assign.
 * @return void
 */
function tool_brcli_backup_set_if_exists(backup_plan $plan, string $settingname, $value): void {
    try {
        $setting = $plan->get_setting($settingname);
        $setting->set_value($value);
    } catch (\Exception $e) {
        // Setting does not exist in this Moodle version or mode.
        $e; // Prevent unused variable warning.
    }
}

// Now get CLI options.
list($options, $unrecognized) = cli_get_params(
    [
        'categoryid'     => false,
        'destination'    => '',
        'preset'         => 'full',
        'users'          => null,
        'questionbank'   => null,
        'calendarevents' => null,
        'competencies'   => null,
        'histories'      => null,
        'logs'           => null,
        'help'           => false,
    ],
    [
        'h' => 'help',
    ]
);

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('unknowoption', 'tool_brcli', $unrecognized));
}

if ($options['help'] || !($options['categoryid']) || !($options['destination'])) {
    echo get_string('helpoptionbck', 'tool_brcli');
    die;
}

$admin = get_admin();
if (!$admin) {
    cli_error(get_string('noadminaccount', 'tool_brcli'));
}

// Normalise and validate destination.
$dir = rtrim($options['destination'], "/\\");
if (empty($dir) || !file_exists($dir) || !is_dir($dir) || !is_writable($dir)) {
    cli_error(get_string('directoryerror', 'tool_brcli'));
}

// Check that the category exists.
if ($DB->count_records('course_categories', ['id' => $options['categoryid']]) == 0) {
    cli_error(get_string('nocategory', 'tool_brcli'));
}

$categoryid = (int) $options['categoryid'];
$courses = $DB->get_records('course', ['category' => $categoryid]);
$amountofcourses = count($courses);

$index = 1;

foreach ($courses as $cs) {
    $bc = new backup_controller(
        backup::TYPE_1COURSE,
        $cs->id,
        backup::FORMAT_MOODLE,
        backup::INTERACTIVE_YES,
        backup::MODE_GENERAL,
        $admin->id
    );

    mtrace(get_string('performingbck', 'tool_brcli', $index . '/' . $amountofcourses));

    // Apply preset and overrides.
    $plan = $bc->get_plan();
    $overrides = [];
    foreach (['users', 'questionbank', 'calendarevents', 'competencies', 'histories', 'logs'] as $name) {
        if ($options[$name] !== null) {
            $overrides[$name] = (int) $options[$name];
        }
    }

    try {
        $settings = \tool_brcli\local\preset::build_settings((string) $options['preset'], $overrides);
    } catch (\InvalidArgumentException $e) {
        $bc->destroy();
        cli_error(get_string('invalidpreset', 'tool_brcli', $options['preset']));
    }

    foreach ($settings as $name => $value) {
        tool_brcli_backup_set_if_exists($plan, $name, $value);
    }

    // Set the default filename.
    $format = $bc->get_format();
    $type = $bc->get_type();
    $id = $bc->get_id();
    $users = 1;
    $anonymised = 0;
    try {
        $users = (int) $plan->get_setting('users')->get_value();
        $anonymised = (int) $plan->get_setting('anonymize')->get_value();
    } catch (\Exception $e) {
        // Settings may not exist; use defaults.
        $e; // Prevent unused variable warning.
    }
    $filename = backup_plan_dbops::get_default_backup_filename($format, $type, $id, $users, $anonymised);
    tool_brcli_backup_set_if_exists($plan, 'filename', $filename);

    // Execution.
    $bc->finish_ui();
    $bc->execute_plan();
    $results = $bc->get_results();
    $file = $results['backup_destination'] ?? null;

    // Store backup to the destination directory if needed.
    if ($file) {
        $target = $dir . '/' . $filename;
        if ($file->copy_content_to($target)) {
            $file->delete();
        } else {
            mtrace(get_string('directoryerror', 'tool_brcli'));
        }
    }
    $bc->destroy();
    $index++;
}

mtrace(get_string('operationdone', 'tool_brcli'));

exit(0);
