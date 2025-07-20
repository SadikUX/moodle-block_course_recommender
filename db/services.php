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
 * Course recommender external services definitions
 *
 * @package    block_course_recommender
 * @copyright  2025 Sadik Mert
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'block_course_recommender_get_courses' => [
        'classname'     => 'block_course_recommender\external',
        'methodname'    => 'get_courses',
        'description'   => 'Get recommended courses based on selected interests',
        'type'         => 'read',
        'ajax'         => true,
        'capabilities' => '',
        'loginrequired' => true,
    ],
];
