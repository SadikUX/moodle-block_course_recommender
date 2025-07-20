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
 * Block block_course_recommender
 * This block allows users to select their interests from a list of tags.
 * and recommends courses based on those interests.
 *
 * @package    block_course_recommender
 * @copyright  2025 Sadik Mert
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_course_recommender extends block_base {
    /**
     * Initializes the block title with the plugin name.
     *
     * This method is called when the block is initialized and sets
     * the block's title using the localized plugin name.
     *
     * @return void
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_course_recommender');
    }

    /**
     * Returns the content of the block.
     *
     * This method generates the HTML content displayed within the block.
     * If the content has already been generated, the cached version is returned.
     *
     * @return stdClass The block content object.
     */
    public function get_content() {
        global $OUTPUT, $DB;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->text = '';

        // Get all tags that can be used for courses.
        // This assumes that tags are used for courses in the system.
        // If you have a different tagging system, adjust accordingly.
        $tags = $DB->get_records_sql("
            SELECT DISTINCT t.id, t.name, t.rawname
            FROM {tag} t
            JOIN {tag_instance} ti ON ti.tagid = t.id
            WHERE ti.itemtype = 'course' AND ti.component = 'core'
            ORDER BY t.name ASC
        ");

        // Array with tag names.
        $interests = [];
        foreach ($tags as $tag) {
            $interests[] = $tag->rawname;
        }

        // Form values from POST and check with sesskey for security.
        if (empty($interests)) {
            $this->content->text .= html_writer::tag('p', get_string('notagsfound', 'block_course_recommender'));
            return $this->content;
        }
        $selected = [];
        if (!empty($_POST) && optional_param('sesskey', '', PARAM_RAW) && confirm_sesskey()) {
            $selected = optional_param_array('interests', [], PARAM_RAW);
        }

        // Building the form HTML.
        $formhtml = html_writer::start_tag('form', [
            'method' => 'post',
            'action' => '',
            'id' => 'courserecommender-form',
        ]);
        // Sesskey as hidden input for security.
        $formhtml .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);

        $formhtml .= html_writer::tag('label', get_string('interest_label', 'block_course_recommender'));
        $formhtml .= html_writer::start_tag('div');

        foreach ($interests as $interest) {
            $attributes = ['type' => 'checkbox', 'name' => 'interests[]', 'value' => $interest, 'class' => 'custom-checkbox'];
            if (in_array($interest, $selected)) {
                $attributes['checked'] = 'checked';
            }
            $formhtml .= html_writer::tag(
                'label',
                html_writer::empty_tag('input', $attributes) . ' ' . s($interest),
                ['class' => 'courserecommender-interests']
            );
        }

        $formhtml .= html_writer::start_div('submit-wrapper'); // Wrapper div for submit button.

        $formhtml .= html_writer::empty_tag('input', [
            'type'  => 'submit',
            'value' => get_string('submit', 'block_course_recommender'),
            'class' => 'btn btn-primary courserecommender-submit',
        ]);

        $formhtml .= html_writer::end_div();

        $formhtml .= html_writer::end_tag('form');

        $this->content->text .= $formhtml;
        $this->content->text .= html_writer::start_div('courserecommender-results') . html_writer::end_div();

        // If user has selected interests, show matching courses.
        if (!empty($selected)) {
            $this->content->text .= html_writer::tag('h4', get_string('matchingcourses', 'block_course_recommender'));

            $courses = $this->find_courses_by_tags($selected);

            if (empty($courses)) {
                $this->content->text .= html_writer::tag('p', get_string('nocourses', 'block_course_recommender'));
            } else {
                global $OUTPUT;

                $tiles = [];
                foreach ($courses as $course) {
                    $image = \core_course\external\course_summary_exporter::get_course_image($course);
                    if (!$image) {
                        $image = 'https://picsum.photos/400/200?random=' . $course->id;
                    }

                    $url = new moodle_url('/course/view.php', ['id' => $course->id]);
                    $title = format_string($course->fullname);

                    $tiles[] = html_writer::start_div('courserecommender-tile card')
                        . html_writer::start_tag('a', ['href' => $url, 'class' => 'courserecommender-link'])
                        . html_writer::empty_tag('img', [
                            'src' => $image,
                            'class' => 'card-img-top courserecommender-img',
                            'alt' => $title,
                        ])
                        . html_writer::start_div('card-body')
                        . html_writer::tag('h5', $title, ['class' => 'card-title courserecommender-title'])
                        . html_writer::end_div()
                        . html_writer::end_tag('a')
                        . html_writer::end_div();
                }

                $this->content->text .= html_writer::start_div('courserecommender-tiles')
                    . implode('', $tiles)
                    . html_writer::end_div();
            }
        }

        return $this->content;
    }

    /**
     * Finds courses that are associated with the given tags.
     *
     * This method searches the database for courses that are tagged
     * with any of the specified tag names.
     *
     * @param array $tags List of tag names to filter courses by.
     * @return array List of matching course objects or records.
     */
    private function find_courses_by_tags(array $tags) {
        global $DB, $USER;

        // Tags to ids mapping.
        list($insql, $params) = $DB->get_in_or_equal($tags, SQL_PARAMS_NAMED, 'tag0');

        $tagrecords = $DB->get_records_select('tag', "name $insql", $params);
        if (empty($tagrecords)) {
            return [];
        }
        $tagids = array_keys($tagrecords);

        $tagidssql = implode(',', array_map('intval', $tagids));

        // SQL to find courses with the selected tags.
        // This assumes that the tags are used for courses in the system.
        // If you have a different tagging system, adjust accordingly.
        $sql = "
            SELECT c.*
            FROM {course} c
            JOIN {tag_instance} ti ON ti.itemid = c.id
            WHERE ti.tagid IN ($tagidssql)
            AND ti.itemtype = 'course'
            AND ti.component = 'core'
            AND c.visible = 1
            GROUP BY c.id
            ORDER BY c.fullname ASC
            LIMIT 20
        ";

        return $DB->get_records_sql($sql);
    }
}
