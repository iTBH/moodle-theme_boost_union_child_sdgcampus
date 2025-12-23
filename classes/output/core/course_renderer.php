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

namespace theme_sdg_boost_union\output\core;

use stdClass;

/**
 * Custom course renderer for the theme_sdg_boost_union theme.
 *
 * Extends core course renderer to provide custom rendering for course-related items.
 *
 * @package    theme_sdg_boost_union
 * @copyright  2024 oncampus GmbH <support@oncampus.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_renderer extends \core_course_renderer {
    /**
     * Renders the activity navigation using a custom template.
     *
     * @param \core_course\output\activity_navigation $page Activity navigation object to render.
     * @return string Rendered HTML for the activity navigation.
     */
    public function render_activity_navigation(\core_course\output\activity_navigation $page) {
        // Export the data to be used by the Mustache template.
        $data = $page->export_for_template($this->output);

        // Replace standard Arrows with Font Awesome Icons - Left Arrow.
        if (!empty($data->prevlink)) {
            // Trim any extra spaces from the text.
            $text = trim($data->prevlink->text);

            // Decode HTML entities to convert encoded characters (e.g., &#x25C0;) to their Unicode representation (e.g., ◀).
            $text = html_entity_decode($text, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, 'UTF-8');

            // Remove the existing left arrow symbol (◀) from the text.
            $text = mb_substr($text, 1);

            // Add the custom Font Awesome icon for the left arrow and update the text.
            $data->prevlink->text = '<i class="fas fa-caret-left"></i> ' . $text;
        }

        // Replace standard Arrows with Font Awesome Icons - Right Arrow.
        if (!empty($data->nextlink)) {
            // Trim any extra spaces from the text.
            $text = trim($data->nextlink->text);

            // Decode HTML entities to convert encoded characters (e.g., &#x25B6;) to their Unicode representation (e.g., ▶).
            $text = html_entity_decode($text, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, 'UTF-8');

            // Remove the existing right arrow symbol (▶) from the text.
            $text = mb_substr($text, 0, -2);

            // Add the custom Font Awesome icon for the right arrow and update the text.
            $data->nextlink->text = $text . ' <i class="fas fa-caret-right"></i>';
        }

        // Render the template with the modified data and return it.
        return $this->output->render_from_template('core_course/activity_navigation', $data);
    }
}
