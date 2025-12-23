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

namespace theme_sdg_boost_union\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_value;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/completionlib.php');

/**
 * Class section_completion
 *
 * Provides an external API to calculate completion percentage for a course section.
 *
 * @package    theme_sdg_boost_union
 * @copyright  2024 oncampus GmbH <support@oncampus.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class section_completion extends external_api {
    /**
     * Defines the parameters for the execute function.
     *
     * @return external_function_parameters Parameters required by the execute function.
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'section' => new external_value(PARAM_INT, 'The section number within the course'),
        ]);
    }

    /**
     * Calculates the completion percentage for a specified section of a course.
     *
     * @param int $courseid The ID of the course.
     * @param int $section The section number within the course.
     * @return float The percentage of completed activities within the section.
     */
    public static function execute(int $section) {
        global $DB, $PAGE, $USER;

        self::validate_parameters(self::execute_parameters(), ['section' => $section]);

        $courseid = $DB->get_field('course_sections', 'course', ['id' => $section]);
        require_login($courseid);

        $course = get_course($courseid);
        $completion = new \completion_info($course);
        $activities = $completion->get_activities();

        $count = 0;
        $all = 0;
        foreach ($activities as $activity) {
            if (!$activity->uservisible || $activity->section != $section) {
                continue;
            }
            // Get progress information and state (we must use get_data because it works for all user roles in course).
            $exporter = new \core_completion\external\completion_info_exporter(
                $course,
                $activity,
                $USER->id,
            );
            $renderer = $PAGE->get_renderer('core');
            $data = (array) $exporter->export($renderer);

            if ($data['state'] == 1) {
                $count++;
            }
            $all++;
        }

        if ($all > 0) {
            $percentage = round(($count / $all) * 100, 0);
        } else {
            $percentage = -1;
        }

        return $percentage;
    }

    /**
     * Defines the return type of the execute function.
     *
     * @return external_value The return type for the execute function.
     */
    public static function execute_returns() {
        return new external_value(PARAM_FLOAT, 'The completion percentage for the section');
    }
}
