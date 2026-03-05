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
 * Restore all .mbz backups from a folder into new courses within a category.
 *
 * @package    tool_brcli
 * @copyright  2019 Paulo Júnior
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir.'/clilib.php');
require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');

/**
 * Safely set a restore plan setting if it exists.
 *
 * @param restore_plan $plan
 * @param string $settingname
 * @param mixed $value
 */
function tool_brcli_restore_set_if_exists(restore_plan $plan, string $settingname, $value): void {
    try {
        $setting = $plan->get_setting($settingname);
        $setting->set_value($value);
    } catch (Exception $e) {
        // Setting does not exist in this Moodle version or mode.
    }
}

// Now get cli options.
list($options, $unrecognized) = cli_get_params(array(
    'categoryid' => false,
    'source' => '',
    'preset' => 'full',
    // Fine-grained overrides (optional).
    'users' => null,
    'questionbank' => null,
    'calendarevents' => null,
    'competencies' => null,
    'histories' => null,
    'logs' => null,
    'help' => false,
    ), array('h' => 'help'));

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('unknowoption', 'tool_brcli', $unrecognized));
}

if ($options['help'] || !($options['categoryid']) || !($options['source'])) {
    echo get_string('helpoptionres', 'tool_brcli');
    die;
}

$admin = get_admin();
if (!$admin) {
    cli_error(get_string('noadminaccount', 'tool_brcli'));
}

$dir = rtrim($options['source'], "/\\");
if (empty($dir) || !file_exists($dir) || !is_dir($dir)) {    
    cli_error(get_string('directoryerror', 'tool_brcli'));
}

// Check that the category exists.
if ($DB->count_records('course_categories', array('id'=>$options['categoryid'])) == 0) {
    cli_error(get_string('nocategory', 'tool_brcli'));
} 


$preset = (string)$options['preset'];

$index = 1;
$sourcefiles = new FilesystemIterator($dir, FilesystemIterator::SKIP_DOTS);
// We count only .mbz files for progress reporting.
$amount_of_courses = 0;
foreach ($sourcefiles as $f) {
    if (strtolower((string)$f->getExtension()) === 'mbz') {
        $amount_of_courses++;
    }
}
// Rewind iterator.
$sourcefiles = new FilesystemIterator($dir, FilesystemIterator::SKIP_DOTS);

foreach ($sourcefiles as $sourcefile) {
    if (strtolower((string)$sourcefile->getExtension()) !== 'mbz') {
        continue;
    }

    mtrace(get_string('performingres', 'tool_brcli', $index . '/' . $amount_of_courses));

    // Extract the file.
    $packer = get_file_packer('application/vnd.moodle.backup');
    $backupid = restore_controller::get_tempdir_name(SITEID, $admin->id);
    $path = "$CFG->tempdir/backup/$backupid/";
    if (!$packer->extract_to_pathname($sourcefile->getPathname(), $path)) {
        mtrace(get_string('invalidbackupfile', 'tool_brcli', $sourcefile->getFilename()));
        $index++;
        continue;
    }

    // Transaction.
    $transaction = $DB->start_delegated_transaction();
 
    // Create new course.
    $folder             = $backupid; // As found in $CFG->dataroot . '/temp/backup/'.
    $categoryid         = (int)$options['categoryid'];
    $userdoingrestore   = $admin->id; // e.g. 2 == admin
    $courseid           = restore_dbops::create_new_course('', '', $categoryid);
 
    // Restore backup into course.
    $controller = new restore_controller(
        $folder,
        $courseid,
        backup::INTERACTIVE_NO,
        backup::MODE_GENERAL,
        $userdoingrestore,
        backup::TARGET_NEW_COURSE
    );

    // Apply preset / overrides.
    $plan = $controller->get_plan();
    $overrides = [];
    foreach (['users', 'questionbank', 'calendarevents', 'competencies', 'histories', 'logs'] as $name) {
        if ($options[$name] !== null) {
            $overrides[$name] = (int)$options[$name];
        }
    }

    try {
        $settings = \tool_brcli\local\preset::build_settings($preset, $overrides);
    } catch (invalid_argument_exception $e) {
        cli_error(get_string('invalidpreset', 'tool_brcli', $preset));
    }

    foreach ($settings as $name => $value) {
        tool_brcli_restore_set_if_exists($plan, $name, $value);
    }

    $precheck = $controller->execute_precheck();
    if ($precheck !== true) {
        try {
            $transaction->rollback(new Exception('Precheck failed'));
        } catch (Exception $e) {
            // Ignore.
        }
        unset($transaction);
        $controller->destroy();
        unset($controller);
        $index++;
        continue;
    }

    $controller->execute_plan();

    $index++;

    // Commit and clean up.
    $transaction->allow_commit();
    unset($transaction);
    $controller->destroy();
    unset($controller);

    // Remove extracted temp backup.
    if (!empty($backupid)) {
        $temppath = $CFG->tempdir . '/backup/' . $backupid;
        if (file_exists($temppath)) {
            fulldelete($temppath);
        }
    }
}

mtrace(get_string('operationdone', 'tool_brcli'));

exit(0);