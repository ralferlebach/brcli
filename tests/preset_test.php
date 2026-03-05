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
 */

declare(strict_types=1);

use tool_brcli\local\preset;

final class tool_brcli_preset_test extends basic_testcase {

    public function test_full_preset_is_empty_by_default(): void {
        $settings = preset::build_settings('full');
        $this->assertSame([], $settings);
    }

    public function test_contentonly_preset_contains_expected_core_flags(): void {
        $settings = preset::build_settings('contentonly');
        $this->assertArrayHasKey('users', $settings);
        $this->assertSame(0, $settings['users']);
        $this->assertSame(0, $settings['questionbank']);
        $this->assertSame(0, $settings['calendarevents']);
        $this->assertSame(0, $settings['competencies']);
    }

    public function test_overrides_take_precedence(): void {
        $settings = preset::build_settings('contentonly', ['users' => 1, 'questionbank' => 1]);
        $this->assertSame(1, $settings['users']);
        $this->assertSame(1, $settings['questionbank']);
    }

    public function test_available_filtering_works(): void {
        $settings = preset::build_settings('contentonly', [], ['users', 'questionbank']);
        $this->assertSame(['users' => 0, 'questionbank' => 0], $settings);
    }

    public function test_invalid_preset_throws(): void {
        $this->expectException(invalid_argument_exception::class);
        preset::build_settings('nope');
    }
}
