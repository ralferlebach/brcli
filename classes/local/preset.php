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
 * Preset + overrides handling for backup/restore plans.
 *
 * Extracted to make CLI behaviour testable and stable across Moodle versions.
 *
 * @package    tool_brcli
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_brcli\local;

use invalid_argument_exception;

final class preset {
    public const PRESET_FULL = 'full';
    public const PRESET_CONTENTONLY = 'contentonly';

    /**
     * Returns plan settings based on preset + overrides.
     *
     * @param string $preset Preset name.
     * @param array $overrides e.g. ['users' => 0]. Values are cast to int.
     * @param null|array $available If provided, only settings in this whitelist are returned.
     * @return array<string,int>
     */
    public static function build_settings(string $preset, array $overrides = [], ?array $available = null): array {
        $preset = strtolower(trim($preset));
        if (!in_array($preset, [self::PRESET_FULL, self::PRESET_CONTENTONLY], true)) {
            throw new invalid_argument_exception('Invalid preset');
        }

        $settings = [];

        if ($preset === self::PRESET_CONTENTONLY) {
            $settings = [
                'users' => 0,
                'role_assignments' => 0,
                'groups' => 0,
                'comments' => 0,
                'badges' => 0,
                'calendarevents' => 0,
                'userscompletion' => 0,
                'histories' => 0,
                'logs' => 0,
                'questionbank' => 0,
                'competencies' => 0,
                'contentbankcontent' => 0,
            ];
        }

        foreach ($overrides as $name => $value) {
            if ($value === null) {
                continue;
            }
            $settings[(string)$name] = (int)$value;
        }

        if ($available !== null) {
            $available = array_flip($available);
            $settings = array_intersect_key($settings, $available);
        }

        return $settings;
    }
}
