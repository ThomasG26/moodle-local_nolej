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
 * Nolej webhook
 *
 * @package     local_nolej
 * @author      Vincenzo Padula <vincenzo@oc-group.eu>
 * @copyright   2024 OC Open Consulting SB Srl
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once (__DIR__ . '/../../config.php');

require_once ($CFG->dirroot . '/local/nolej/classes/api.php');

use local_nolej\api;

// Deliver file if exists (public for 2 hours, after that the user need to be logged in).
$timelimit = 2 * 3600;
$filename = optional_param('fileid', null, PARAM_FILE);
$documentid = optional_param('documentid', null, PARAM_ALPHANUMEXT);

if ($filename != null) {
    $filename = api::sanitizefilename($filename);
    $dir = $documentid == null ? api::uploaddir() : api::datadir($documentid);
    $dest = $dir . '/' . $filename;

    if (file_exists($dest) && is_file($dest)) {
        $owner = strstr(basename($dest), '.', true);
        $timepassed = time() - filemtime($dest);
        if (
            $timepassed < $timelimit ||
            (isloggedin() && $owner == $USER->id)
        ) {
            api::deliverfile($dest);
        }
    }

    exit('Forbidden ' . $dest);
}

// Parse POST data.
$nolej = new api();
$nolej->parse();
exit();
