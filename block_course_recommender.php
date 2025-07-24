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
     * Get the course image URL
     *
     * @param stdClass $course
     * @return string image url
     */
    protected function get_course_image_url($course) {
        global $OUTPUT;

        return $OUTPUT->get_generated_image_for_id($course->id);
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

        // Setup the AMD module.
        $this->page->requires->js_call_amd('block_course_recommender/recommender', 'init');

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

        if (empty($interests)) {
            $this->content->text .= html_writer::tag('p', get_string('notagsfound', 'block_course_recommender'));
            return $this->content;
        }
        $selected = [];
        if (!empty($_POST)) {
            $selected = optional_param_array('interests', [], PARAM_RAW);
        }

        // Prepare data for Mustache template.
        $tagsdata = [];
        foreach ($interests as $tagname) {
            $tagsdata[] = [
                'name' => $tagname,
                'checked' => in_array($tagname, $selected),
                'id' => 'interest-' . clean_param($tagname, PARAM_ALPHANUMEXT),
            ];
        }
        $data = [
            'interestlabel' => get_string('interest_label', 'block_course_recommender'),
            'tags' => $tagsdata,
            'all_tags_json' => json_encode($interests),
        ];
        $this->content->text .= $OUTPUT->render_from_template('block_course_recommender/tagform', $data);
        $this->content->text .= html_writer::start_div('courserecommender-results') . html_writer::end_div();
        return $this->content;
    }
}
