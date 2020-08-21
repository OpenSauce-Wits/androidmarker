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
 * A scheduled task.
 *
 * @package assignfeedback_androidmarker
 * @author Thando Peter (thandopeter8@gmail.com)
 */
namespace assignfeedback_androidmarker\task;

use core\task\scheduled_task;
//use assignfeedback_editpdf\document_services;
//use assignfeedback_editpdf\combined_document;
use context_module;
use assign;

/**
 * Task to find submission that have to be marked and send them to a server.
 */
class mark_submissions extends scheduled_task {
    // Database table names.
    const TABLE_ASSIGNFEEDBACK_ANDROIDMARKER = "assignfeedback_androidmarker";
    const TABLE_ANDROIDMARKER_TESTRESULT = "androidmarker_testresult";
    const TABLE_ANDROIDMARKER_COMPILATIONERROR = "androidmarker_errors";

    const COMPONENT_NAME = "assignfeedback_androidmarker";

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('marksubmissions', 'assignfeedback_androidmarker');
    }

    /**
     * Do the job.
     * Throw exceptions on errors (the job will be retried).
     */
    public function execute() {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/mod/assign/locallib.php');
        require_once($CFG->dirroot . '/mod/assign/lib.php');


    }
}
