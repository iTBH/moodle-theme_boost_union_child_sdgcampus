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

namespace theme_sdg_boost_union\output;

use theme_sdg_boost_union\activitymod;
use theme_sdg_boost_union\navigation_helper;
use theme_sdg_boost_union\sectionmod;

/**
 * Custom core renderer for the theme_sdg_boost_union theme.
 *
 * Extends core renderer to provide custom navigation and activity rendering.
 *
 * @package    theme_sdg_boost_union
 * @copyright  2024 oncampus GmbH <support@oncampus.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class core_renderer extends \theme_boost_union\output\core_renderer {
    /**
     * Renders the activity navigation with customized next and previous links.
     *
     * @return string Rendered HTML for the activity navigation or an empty string if navigation is not shown.
     */
    public function activity_navigation() {
        global $DB;

        // First we should check if we want to add navigation.
        $context = $this->page->context;
        if (
            ($this->page->pagelayout !== 'incourse' && $this->page->pagelayout !== 'frametop')
            || $context->contextlevel != CONTEXT_MODULE
        ) {
            return '';
        }

        // If the activity is in stealth mode, show no links.
        if ($this->page->cm->is_stealth()) {
            return '';
        }

        $course = $this->page->cm->get_course();
        $courseformat = course_get_format($course);

        // If the theme implements course index and the current course format uses course index and the current
        // page layout is not 'frametop' (this layout does not support course index), show no links.
        if (
            $this->page->theme->usescourseindex && $courseformat->uses_course_index() &&
            $this->page->pagelayout !== 'frametop'
        ) {
            return '';
        }

        // Get a list of all the activities in the course.
        $modules = get_fast_modinfo($course->id)->get_cms();

        // Put the modules into an array in order by the position they are shown in the course.
        $mods = [];
        $activitylist = [];
        foreach ($modules as $module) {
            // Only add activities the user can access, aren't in stealth mode and have a url (eg. mod_label does not).
            if (!$module->uservisible || $module->is_stealth() || empty($module->url)) {
                continue;
            }
            $mods[$module->id] = $module;

            // No need to add the current module to the list for the activity dropdown menu.
            if ($module->id == $this->page->cm->id) {
                continue;
            }

            // Module name.
            $modname = $module->get_formatted_name();
            // Display the hidden text if necessary.
            if (!$module->visible) {
                $modname .= ' ' . get_string('hiddenwithbrackets');
            }

            // Module URL.
            $linkurl = new \moodle_url($module->url, ['forceview' => 1]);
            // Add module URL (as key) and name (as value) to the activity list array.
            $activitylist[$linkurl->out(false)] = $modname;
        }

        $nummods = count($mods);

        // If there is only one mod then do nothing.
        if ($nummods == 1) {
            return '';
        }

        // Get an array of just the course module ids used to get the cmid value based on their position in the course.
        $modids = array_keys($mods);

        // Get the position in the array of the course module we are viewing.
        $position = array_search($this->page->cm->id, $modids);

        $sql = "SELECT MAX(section) FROM {course_sections} WHERE course = :courseid";
        $maxsectionnum = $DB->get_field_sql($sql, ['courseid' => $this->page->course->id]);

        $sectionmodjumps = false;
        if ($this->page->course->format == 'topics') {
            $params = [
                'courseid' => $this->page->course->id,
                'sectionid' => 0,
                'name' => 'coursedisplay',
            ];
            $sectionmodjumps = $DB->get_record('course_format_options', $params)->value == 1;
        }

        $navigationlinks = navigation_helper::get_navigation_links($course->id, $this->page->cm->id, 'activity', $sectionmodjumps);

        $prevmod = !empty($navigationlinks['prevElement'])
            ? self::create_mod($navigationlinks['prevElement'], $course->id, false)
            : null;

        $nextmod = !empty($navigationlinks['nextElement'])
            ? self::create_mod($navigationlinks['nextElement'], $course->id, true)
            : null;

        $activitynav = new \core_course\output\activity_navigation($prevmod, $nextmod, $activitylist);
        $renderer = $this->page->get_renderer('core', 'course');
        return $renderer->render($activitynav);
    }

    /**
     * Creates a module object based on the type of navigation element (section or activity).
     *
     * This function takes an element from the navigation links and determines whether it is a
     * section or an activity. Based on the type, it returns a sectionmod or activitymod object
     * representing the previous or next module for navigation.
     *
     * @param array $element The navigation element, containing type and ID of the section or activity.
     * @param int $courseid The ID of the course in which the navigation element resides.
     * @param bool $isnext Indicates if the module is the next item in navigation (true) or the previous item (false).
     *
     * @return sectionmod|activitymod|null Returns a sectionmod object if the element is a section,
     *         an activitymod object if it is an activity, or null if the type is unsupported.
     */
    public static function create_mod($element, $courseid, $isnext) {
        $modinfo = get_fast_modinfo($courseid);
        $courseformat = course_get_format(get_course($courseid));

        if ($element['type'] === 'section') {
            $sectioninfo = $modinfo->get_section_info_by_id($element['id']);
            $sectionnum = $sectioninfo->section;
            return new sectionmod($courseformat->get_section($sectionnum), $isnext);
        } else if ($element['type'] === 'activity') {
            $cm = $modinfo->get_cm($element['id']);
            return new activitymod($cm, $isnext);
        }
        return null;
    }


    /**
     * Generates and renders the navigation bar with customized text for course nodes.
     *
     * This method creates a navigation bar object, retrieves its items, and modifies the text,
     * short text, and title of course-type navigation nodes to provide localized course overview text.
     * It then renders the navigation bar using a specified template.
     *
     * @return string The rendered HTML output of the customized navigation bar.
     * @throws \coding_exception
     * @throws \core\exception\moodle_exception
     */
    public function navbar(): string {
        $newnav = new \theme_boost_union\boostnavbar($this->page);

        $items = $newnav->get_items();

        foreach ($items as $item) {
            if ($item->type === \navigation_node::TYPE_COURSE) {
                $courseoverview = get_string('courseoverview', 'theme_sdg_boost_union');
                $item->text = $courseoverview;
                $item->shorttext = $courseoverview;
                $item->title = $courseoverview;
            }
        }

        return $this->render_from_template('core/navbar', $newnav);
    }
}
