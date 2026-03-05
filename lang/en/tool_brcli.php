<?php
/**
 * admin tool brcli
 * Backup & restore command line interface
 * @package admin
 * @subpackage tool
 * @author Paulo Júnior <pauloa.junior@ufla.br>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['pluginname'] = 'Backup and Restore Command-Line Interface';
$string['unknowoption'] = 'Unknow option: {$a}';
$string['noadminaccount'] = 'Error: No admin account was found!';
$string['directoryerror'] = 'Error: Destination directory does not exists or not writable!';
$string['nocategory'] = 'Error: No category was found!';
$string['performingbck'] = 'Performing backup of the {$a} course...';
$string['performingres'] = 'Restoring backup of the {$a} course...';
$string['operationdone'] = 'Done!';
$string['invalidbackupfile'] = 'Invalid backup file: {$a}';
$string['invalidpreset'] = 'Invalid preset: {$a}. Supported values: full, contentonly.';
$string['helpoptionbck'] = 
'Perform backup of the courses of a specific category.

Options:
--categoryid=INTEGER        Category ID for backup.
--destination=STRING        Path where to store backup file. 
--preset=STRING             Backup preset. full (default) or contentonly.
--users=0|1                 Override: include user data.
--questionbank=0|1          Override: include question bank.
--calendarevents=0|1        Override: include calendar events.
--competencies=0|1          Override: include competencies.
--histories=0|1             Override: include grade histories.
--logs=0|1                  Override: include logs.
-h, --help                  Print out this help.

Example:
    sudo -u www-data /usr/bin/php admin/tool/brcli/backup.php --categoryid=1 --destination=/moodle/backup/

    # Content-only backups (no users, question bank, calendar, competencies, logs, histories, etc.)
    sudo -u www-data /usr/bin/php admin/tool/brcli/backup.php --categoryid=1 --destination=/moodle/backup/ --preset=contentonly
';
$string['helpoptionres'] = 
'Restore all backup files belong to a specific folder.

Options:
--categoryid=INTEGER        Category ID where the backup must be restored.
--source=STRING             Path where the backup files (.mbz) are. 
--preset=STRING             Restore preset. full (default) or contentonly.
--users=0|1                 Override: restore user data.
--questionbank=0|1          Override: restore question bank.
--calendarevents=0|1        Override: restore calendar events.
--competencies=0|1          Override: restore competencies.
--histories=0|1             Override: restore grade histories.
--logs=0|1                  Override: restore logs.
-h, --help                  Print out this help.

Example:
    sudo -u www-data /usr/bin/php admin/tool/brcli/restore.php --categoryid=1 --source=/moodle/backup/

    # Restore as content-only (ignore user data, question bank, calendar, competencies, logs, histories, etc.)
    sudo -u www-data /usr/bin/php admin/tool/brcli/restore.php --categoryid=1 --source=/moodle/backup/ --preset=contentonly
';
