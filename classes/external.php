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

namespace block_course_recommender;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_value;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core\context\system as context_system;
use moodle_url;
use moodle_exception;

/**
 * Course recommender external API
 *
 * @package    block_course_recommender
 * @copyright  2025 Sadik Mert
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class external extends external_api {

    /**
     * Returns description of get_courses parameters
     *
     * @return external_function_parameters
     */
    public static function get_courses_parameters() {
        return new external_function_parameters([
            'interests' => new external_multiple_structure(
                new external_value(PARAM_TEXT, 'Interest tag'),
                'List of selected interests',
                VALUE_DEFAULT,
                []
            ),
            'sesskey' => new external_value(PARAM_RAW, 'Session key for security'),
        ]);
    }

    /**
     * Returns description of get_courses result value
     *
     * @return external_single_structure
     */
    public static function get_courses_returns() {
        return new external_single_structure([
            'html' => new external_value(PARAM_RAW, 'HTML content for the courses list'),
        ]);
    }

    /**
     * Get courses based on interests
     *
     * @param array $interests List of interests
     * @param string $sesskey Session key
     * @return array
     */
    public static function get_courses($interests, $sesskey) {
        global $DB, $PAGE;

        // Set up the page context.
        $context = context_system::instance();
        $PAGE->set_context($context);
        self::validate_context($context);
        require_capability('moodle/course:view', $context);

        $params = self::validate_parameters(self::get_courses_parameters(), [
            'interests' => $interests,
            'sesskey' => $sesskey,
        ]);

        // Validate session key.
        if (!confirm_sesskey($params['sesskey'])) {
            throw new moodle_exception('invalidsesskey', 'error');
        }

        // Find courses.
        if (empty($params['interests'])) {
            return ['html' => ''];
        }

        // Tags to ids mapping.
        list($insql, $inparams) = $DB->get_in_or_equal($params['interests'], SQL_PARAMS_NAMED, 'tag');
        $tagrecords = $DB->get_records_select('tag', "rawname $insql", $inparams);

        if (empty($tagrecords)) {
            return ['html' => '<div class="alert alert-info">' . get_string('nocourses', 'block_course_recommender') . '</div>'];
        }

        $tagids = array_keys($tagrecords);
        list($tagidssql, $tagidparams) = $DB->get_in_or_equal($tagids, SQL_PARAMS_NAMED, 'tag');
        list($tagidssql2, $tagidparams2) = $DB->get_in_or_equal($tagids, SQL_PARAMS_NAMED, 'tag2');
        $sql = "
            WITH matching_courses AS (
                SELECT DISTINCT c.id
                FROM {course} c
                JOIN {tag_instance} ti ON ti.itemid = c.id
                WHERE ti.tagid $tagidssql
                AND ti.itemtype = 'course'
                AND ti.component = 'core'
                AND c.visible = 1
            )
            SELECT c.*, GROUP_CONCAT(t.rawname) as tagnames,
                   COUNT(CASE WHEN t.id $tagidssql2 THEN 1 END) as matching_tags
            FROM {course} c
            JOIN matching_courses mc ON mc.id = c.id
            LEFT JOIN {tag_instance} ti ON ti.itemid = c.id
                AND ti.itemtype = 'course'
                AND ti.component = 'core'
            LEFT JOIN {tag} t ON t.id = ti.tagid
            GROUP BY c.id
            ORDER BY matching_tags DESC, c.timecreated DESC
            LIMIT 20
        ";
        $params = array_merge($tagidparams, $tagidparams2);
        $courses = $DB->get_records_sql($sql, $params);

        $data = [
            'matchingcourses' => get_string('matchingcourses', 'block_course_recommender'),
            'nocourses' => get_string('nocourses', 'block_course_recommender'),
        ];
        if (!empty($courses)) {
            $data['courses'] = [
                'list' => [],
            ];
            foreach ($courses as $course) {
                $url = new \moodle_url('/course/view.php', ['id' => $course->id]);
                $title = format_string($course->fullname);
                $courseobj = new \core_course_list_element($course);
                $image = \core_course\external\course_summary_exporter::get_course_image($courseobj);
                if (empty($image)) {
                    $image = 'https://picsum.photos/400/200?random=' . $course->id;
                }
                $tags = array_map('trim', explode(',', $course->tagnames));
                $data['courses']['list'][] = [
                    'url' => $url->out(false),
                    'title' => $title,
                    'image' => $image,
                    'tags' => $tags,
                ];
            }
        }
        global $OUTPUT;
        $html = $OUTPUT->render_from_template('block_course_recommender/courses', $data);
        return ['html' => $html];
    }
}
