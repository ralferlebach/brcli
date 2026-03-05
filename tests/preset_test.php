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
 * Unit tests for tool_brcli presets.
 *
 * @package    tool_brcli
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

declare(strict_types=1);

namespace tool_brcli;

use tool_brcli\local\preset;

/**
 * Preset unit tests.
 *
 * @package    tool_brcli
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @coversDefaultClass \tool_brcli\local\preset
 */
final class preset_test extends \basic_testcase {

    /**
     * Test that the full preset returns an empty settings array by default.
     *
     * @covers ::build_settings
     * @return void
     */
    public function test_full_preset_is_empty_by_default(): void {
        $settings = preset::build_settings('full');
        $this->assertSame([], $settings);
    }

    /**
     * Test that the contentonly preset contains the expected core flags.
     *
     * @covers ::build_settings
     * @return void
     */
    public function test_contentonly_preset_contains_expected_core_flags(): void {
        $settings = preset::build_settings('contentonly');
        $this->assertArrayHasKey('users', $settings);
        $this->assertSame(0, $settings['users']);
        $this->assertSame(0, $settings['questionbank']);
        $this->assertSame(0, $settings['calendarevents']);
        $this->assertSame(0, $settings['competencies']);
    }

    /**
     * Test that overrides take precedence over preset defaults.
     *
     * @covers ::build_settings
     * @return void
     */
    public function test_overrides_take_precedence(): void {
        $settings = preset::build_settings('contentonly', ['users' => 1, 'questionbank' => 1]);
        $this->assertSame(1, $settings['users']);
        $this->assertSame(1, $settings['questionbank']);
    }

    /**
     * Test that filtering by available settings works correctly.
     *
     * @covers ::build_settings
     * @return void
     */
    public function test_available_filtering_works(): void {
        $settings = preset::build_settings('contentonly', [], ['users', 'questionbank']);
        $this->assertSame(['users' => 0, 'questionbank' => 0], $settings);
    }

    /**
     * Test that an invalid preset name throws an exception.
     *
     * @covers ::build_settings
     * @return void
     */
    public function test_invalid_preset_throws(): void {
        $this->expectException(\InvalidArgumentException::class);
        preset::build_settings('nope');
    }
}
