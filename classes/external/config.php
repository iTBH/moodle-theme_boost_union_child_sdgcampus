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
 * External class for retrieving configuration settings.
 * @package    theme_sdg_boost_union
 * @copyright  2023 Daniel Poggenpohl <daniel.poggenpohl@fernuni-hagen.de> and Alexander Bias <bias@alexanderbias.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_sdg_boost_union\external;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/externallib.php');


use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_value;

/**
 * Class config
 *
 * Provides an external API for retrieving configuration settings for the theme_sdg_boost_union theme.
 *
 * @package    theme_sdg_boost_union
 * @copyright  2024 oncampus GmbH <support@oncampus.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class config extends external_api {
    /**
     * Defines the parameters for the execute function.
     *
     * @return external_function_parameters Parameters required by the execute function.
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'configname' => new external_value(PARAM_TEXT, 'The name of the configuration setting to retrieve'),
        ]);
    }

    /**
     * Retrieves the specified configuration setting for the theme.
     *
     * @param string $configname The name of the configuration setting to retrieve.
     * @return mixed The value of the requested configuration setting.
     */
    public static function execute(string $configname) {
        // Validate the input parameters.
        self::validate_parameters(self::execute_parameters(), ['configname' => $configname]);

        // Return the configuration value for the specified setting.
        return get_config('theme_sdg_boost_union', $configname);
    }

    /**
     * Defines the return type of the execute function.
     *
     * @return external_value The return type for the execute function.
     */
    public static function execute_returns() {
        return new external_value(PARAM_RAW, 'The value of the requested configuration setting');
    }
}
