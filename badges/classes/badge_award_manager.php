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

namespace core_badges;

/**
 * Badge award manager class.
 *
 * @package    core_badges
 * @copyright  2012 onwards Totara Learning Solutions Ltd {@link http://www.totaralms.com/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Yuliya Bozhko <yuliya.bozhko@totaralms.com>
 * @author     Dai Nguyen Trong <ngtrdai@hotmail.com>
 */
class badge_award_manager {
    /**
     * Process manual badge award.
     *
     * @param int $recipientid
     * @param int $issuerid
     * @param int $issuerrole
     * @param int $badgeid
     * @return bool
     */
    public static function process_manual_award(int $recipientid, int $issuerid, int $issuerrole, int $badgeid): bool {
        global $DB;

        $params = [
            'badgeid' => $badgeid,
            'issuerid' => $issuerid,
            'issuerrole' => $issuerrole,
            'recipientid' => $recipientid,
        ];

        if (!$DB->record_exists('badge_manual_award', $params)) {
            $award = new \stdClass();
            $award->badgeid = $badgeid;
            $award->issuerid = $issuerid;
            $award->issuerrole = $issuerrole;
            $award->recipientid = $recipientid;
            $award->datemet = time();

            return (bool)$DB->insert_record('badge_manual_award', $award);
        }

        return false;
    }

    /**
     * Process manual badge revocation.
     *
     * @param int $recipientid
     * @param int $issuerid
     * @param int $issuerrole
     * @param int $badgeid
     * @return bool
     * @throws \moodle_exception
     */
    public static function process_manual_revoke(int $recipientid, int $issuerid, int $issuerrole, int $badgeid): bool {
        global $DB;

        $params = [
            'badgeid' => $badgeid,
            'issuerid' => $issuerid,
            'issuerrole' => $issuerrole,
            'recipientid' => $recipientid,
        ];

        if (!$DB->record_exists('badge_manual_award', $params)) {
            throw new \moodle_exception('error:badgenotfound', 'badges');
        }

        $success = $DB->delete_records('badge_manual_award', [
            'badgeid' => $badgeid,
            'issuerid' => $issuerid,
            'recipientid' => $recipientid,
        ]);

        $success &= $DB->delete_records('badge_issued', [
            'badgeid' => $badgeid,
            'userid' => $recipientid,
        ]);

        if ($success) {
            $badge = new \badge($badgeid);
            $eventparams = [
                'objectid' => $badgeid,
                'relateduserid' => $recipientid,
                'context' => $badge->get_context(),
            ];
            $event = \core\event\badge_revoked::create($eventparams);
            $event->trigger();
        }

        return (bool)$success;
    }
}
