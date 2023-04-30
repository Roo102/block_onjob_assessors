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
 * onjob_assessors block caps.
 *
 * @package    block_onjob_assessors
 * @copyright  Andrew Chandler <andrewc@etco.co.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir.'/tablelib.php');

class block_onjob_assessors extends block_list {

    function has_config() {
        return true;
    }

    function init() {
        $this->title = get_string('blocktitle', 'block_onjob_assessors');
    }

    function get_content() {
        global $CFG, $OUTPUT, $USER, $DB;

        if ($this->content !== null) {
            return $this->content;
        }

        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';

        // Check if user has capability to view the block.
        if (has_capability('block/onjob_assessors:view', context_block::instance($this->instance->id))) {

            // Create SQL query and pass in params.
            // SQL query returns a row only where logged in user is allocated to mark an assignment submission
            // in this course and final grade for the submission is null or less than 100%.
            $sql = "SELECT uf.id AS ufid, uf.userid, u.firstname, u.lastname,
                        uf.assignment, uf.allocatedmarker, uf.workflowstate, a.name,
                        asub.status, gg.finalgrade, cm.id AS coursemodule, asub.timecreated
                    FROM {assign_user_flags} uf, {assign} a, {assign_submission} asub,
                        {grade_items} gi, {grade_grades} gg, {course_modules} cm, {user} u
                    WHERE (uf.assignment = a.id AND uf.userid = asub.userid
                        AND uf.assignment = asub.assignment AND gi.iteminstance = a.id
                        AND gi.itemmodule = 'assign' AND gg.itemid = gi.id
                        AND gg.userid = uf.userid AND cm.instance = a.id
                        AND cm.module = 1 AND asub.latest = 1 AND u.id = uf.userid)
                        AND (gg.finalgrade < 100 OR gg.finalgrade IS NULL)
                        AND uf.allocatedmarker = :marker
                        AND a.course = :courseid AND gi.courseid = a.course AND cm.course = a.course
                    ORDER BY uf.assignment, asub.timecreated, uf.userid";
            $sqlparams = array('marker'=>$USER->id, 'courseid'=>$this->page->course->id);

            // Fetch data from DB
            if ($allocations = $DB->get_records_sql($sql, $sqlparams)) {

                // Get config settings values.
                $config = get_config('onjob_assessors');
                $warndays = $config->warndays;
                $overdays = $config->overdays;
                $warncolour = $config->warncolour;
                $overcolour = $config->overcolour;

                // Convert settings day values to seconds for use with DB timestamps (number of days * 86400).
                $warnsecs = $warndays * 86400;
                $oversecs = $overdays * 86400;

                // Set warning and overdue highlight colour styles for inline CSS.
                $warnstyle = 'color: ' . $warncolour . ';';
                $overstyle = 'color: ' . $overcolour . ';';

                // Set up dynamic help description.
                $help = '<p style="font-size:75%;">The list below shows assessments assigned to you for marking in order of assessment, then date submitted, each line links directly to the assessment.
                Links in <span style="color: ' . $warncolour . ';"><b>' . $warncolour . '</b></span> have been submitted <u>more than ' . $warndays . ' days ago</u>.
                Links in <span style="color: ' . $overcolour . ';"><b>' . $overcolour . '</b></span> have been submitted <u>more than ' . $overdays . ' days ago</u>.</p><hr>';

                // Convert help string to html and set as first list item.
                $this->content->items[] = text_to_html($help);

                // Set timestamp to check age of submission.
                $now = new DateTime("now", core_date::get_user_timezone_object());
                $timestamp = $now->getTimestamp();

                // Process each row from SQL query.
                foreach ($allocations as $allocation) {

                    // Calculate age of submission
                    $age = intval($timestamp)-intval($allocation->timecreated);

                    // Create list item for each row returned from DB ([Assignment name] - [student name] ([submission date]))
                    $listitem = explode('O', $allocation->name, 2)[0] . '- ' . $allocation->firstname . ' ' . $allocation->lastname
                        . ' (' . userdate($allocation->timecreated, get_string('strftimedatefullshort', 'core_langconfig')) . ')';

                    // Change link colour to orange (warncolour default) where submission created more than 15 days (warndays default) ago
                    // or red (overcolour default) where submission created more than 30 days (overdays default) ago.
                    if ($age > $oversecs) {
                        $style = array('style' => $overstyle);
                    } else if ($age > $warnsecs) {
                        $style = array('style' => $warnstyle);
                    }else {
                        $style = null;
                    }

                    // Create link assignment submission.
                    $this->content->items[] = html_writer::link(new moodle_url('/mod/assign/view.php',
                        ['id' => $allocation->coursemodule, 'rownum' => 0, 'action' => 'grader', 'userid' => $allocation->userid]),
                        $listitem, $style);
                }
            } else {
                // Nothing returned from SQL query, display 'empty' help string instead.
                $this->content->items[] = get_string('empty', 'block_onjob_assessors');
            }

            // user/index.php expect course context, so get one if page has module context.
            $currentcontext = $this->page->context->get_course_context(false);

            if (empty($currentcontext)) {
                return $this->content;
            }
            if ($this->page->course->id == SITEID) {
                $this->content->text .= "site context";
            }

        } else {
            // If user doesn't have view capability, return empty content, hiding the block.
            $this->content->text = '';
        }

        return $this->content;
    }

}
