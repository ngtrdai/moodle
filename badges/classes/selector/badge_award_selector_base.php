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

namespace core_badges\selector;

use context_course;
use context_system;

require_once($CFG->dirroot . '/user/selector/lib.php');

/**
 * Base class for badge award selectors.
 *
 * @package    core_badges
 * @copyright  2012 onwards Totara Learning Solutions Ltd {@link http://www.totaralms.com/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Yuliya Bozhko <yuliya.bozhko@totaralms.com>
 * @author     Dai Nguyen Trong <ngtrdai@hotmail.com>
 */
class badge_award_selector_base extends \user_selector_base {
    /**
     * @var ?int The id of the badge this selector is being used for.
     */
    protected ?int $badgeid = null;

    /**
     * @var ?object The context of the badge this selector is being used for.
     */
    protected ?object $context = null;

    /**
     * @var ?int The id of the role of badge issuer in current context.
     */
    protected ?int $issuerrole = null;

    /**
     * @var ?int The id of badge issuer.
     */
    protected ?int $issuerid = null;

    /**
     * @var ?string $url The return address. Accepts either a string or a moodle_url.
     */
    public ?string $url;

    /**
     * @var ?int The current group being displayed.
     */
    public ?int $currentgroup;

    /**
     * Constructor method.
     *
     * @param string $name
     * @param array $options
     */
    public function __construct(string $name, array $options) {
        $options['accesscontext'] = $options['context'];
        parent::__construct($name, $options);

        if (isset($options['context'])) {
            if ($options['context'] instanceof context_system) {
                // If it is a site badge, we need to get context of frontpage.
                $this->context = context_course::instance(SITEID);
            } else {
                $this->context = $options['context'];
            }
        }

        $this->badgeid = $options['badgeid'] ?? null;
        $this->issuerid = $options['issuerid'] ?? null;
        $this->issuerrole = $options['issuerrole'] ?? null;
        $this->url = $options['url'] ?? null;
        $this->currentgroup = $options['currentgroup'] ?? groups_get_course_group($COURSE, true);
    }

    /**
     * Returns an array of options to seralise and store for searches.
     *
     * @return array
     */
    protected function get_options(): array {
        $options = parent::get_options();
        $options['file'] = 'badges/classes/selector/badge_award_selector_base.php';
        $options['context'] = $this->context;
        $options['badgeid'] = $this->badgeid;
        $options['issuerid'] = $this->issuerid;
        $options['issuerrole'] = $this->issuerrole;

        // These will be used to filter potential badge recipients when searching.
        $options['currentgroup'] = $this->currentgroup;

        return $options;
    }

    /**
     * Restricts the selection of users to display, according to the groups they belong.
     *
     * @return array
     */
    protected function get_groups_sql(): array {
        $groupsql = '';
        $groupwheresql = '';
        $groupwheresqlparams = [];

        if ($this->currentgroup) {
            $groupsql = ' JOIN {groups_members} gm ON gm.userid = u.id ';
            $groupwheresql = ' AND gm.groupid = :gr_grpid ';
            $groupwheresqlparams = ['gr_grpid' => $this->currentgroup];
        }

        return [$groupsql, $groupwheresql, $groupwheresqlparams];
    }

    #[\Override]
    public function find_users($search): array {
        return [];
    }
}
