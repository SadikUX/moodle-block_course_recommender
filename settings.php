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
 * Settings for the Course Recommender block
 *
 * @package    block_course_recommender
 * @copyright  2025 Sadik Mert
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configcolourpicker(
        'block_course_recommender/tagcolor',
        get_string('tagcolor', 'block_course_recommender'),
        get_string('tagcolor_desc', 'block_course_recommender'),
        '#0073e6'
    ));

    $settings->add(new admin_setting_configtext(
        'block_course_recommender/maxtags',
        get_string('maxtags', 'block_course_recommender'),
        get_string('maxtags_desc', 'block_course_recommender'),
        0,
        PARAM_INT
    ));

    $settings->add(new admin_setting_configselect(
        'block_course_recommender/tagsort',
        get_string('tagsort', 'block_course_recommender'),
        get_string('tagsort_desc', 'block_course_recommender'),
        'popularity',
        [
            'popularity' => get_string('tagsort_popularity', 'block_course_recommender'),
            'az' => get_string('tagsort_az', 'block_course_recommender'),
            'za' => get_string('tagsort_za', 'block_course_recommender'),
        ]
    ));
}
