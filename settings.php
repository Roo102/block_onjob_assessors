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

$settings->add(new admin_setting_heading('onjobheader',
                                         get_string('settingsheader', 'block_onjob_assessors'),
                                         get_string('settingsheaderdesc', 'block_onjob_assessors')));

$settings->add(new admin_setting_configtext('onjob_assessors/warndays',
                                             get_string('warndayslabel', 'block_onjob_assessors'),
                                             get_string('warndaysdesc', 'block_onjob_assessors'),
                                             15, PARAM_INT));

$settings->add(new admin_setting_configtext('onjob_assessors/warncolour',
                                             get_string('warncolourlabel', 'block_onjob_assessors'),
                                             get_string('warncolourdesc', 'block_onjob_assessors'),
                                             'orange'));

$settings->add(new admin_setting_configtext('onjob_assessors/overdays',
                                             get_string('overdayslabel', 'block_onjob_assessors'),
                                             get_string('overdaysdesc', 'block_onjob_assessors'),
                                             30, PARAM_INT));

$settings->add(new admin_setting_configtext('onjob_assessors/overcolour',
                                             get_string('overcolourlabel', 'block_onjob_assessors'),
                                             get_string('overcolourdesc', 'block_onjob_assessors'),
                                             'red'));
