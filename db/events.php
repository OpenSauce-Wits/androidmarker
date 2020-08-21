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
 * EditPDF event handler definition.
 *
 * @package assignfeedback_androidmarker
 * @category event
 * @author Thando Peter (thandopeter8@gmail.com)
 */

// List of observers.
$observers = array(
    array(
        'eventname'   => 'assignsubmission_file\event\submission_created',
        'callback'    => '\assignfeedback_androidmarker\event\observer::submission_created',
    ),
    array(
        'eventname'   => 'assignsubmission_file\event\submission_updated',
        'callback'    => '\assignfeedback_androidmarker\event\observer::submission_updated',
    ),
);
