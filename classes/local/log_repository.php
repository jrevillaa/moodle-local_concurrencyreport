<?php
// This file is part of Moodle - http://moodle.org/

namespace local_concurrencyreport\local;

defined('MOODLE_INTERNAL') || die();

class log_repository {
    public function get_activity_recordset(array $filters) {
        global $DB;

        $sql = "SELECT l.userid, l.timecreated
                  FROM {logstore_standard_log} l
                  JOIN {user} u
                    ON u.id = l.userid
                   AND u.deleted = 0
                   AND u.suspended = 0
                   AND u.confirmed = 1
                 WHERE l.timecreated BETWEEN :eventfrom AND :eventto
                   AND l.userid > 0
                   AND l.anonymous = 0
                   AND u.username <> :guestusername
                   AND (l.action IS NULL OR l.action <> :loggedoutaction)";

        $params = array(
            'eventfrom' => max(0, (int)$filters['from'] - (int)$filters['window']),
            'eventto' => (int)$filters['to'],
            'guestusername' => 'guest',
            'loggedoutaction' => 'loggedout',
        );

        if ($filters['audience'] === filter_manager::AUDIENCE_STUDENTS) {
            $studentroleids = $this->get_student_role_ids();

            if (empty($studentroleids)) {
                return new \ArrayIterator(array());
            }

            list($rolesql, $roleparams) = $DB->get_in_or_equal($studentroleids, SQL_PARAMS_NAMED, 'studentrole');
            $sql .= " AND EXISTS (
                        SELECT 1
                          FROM {role_assignments} ra
                         WHERE ra.userid = l.userid
                           AND ra.roleid {$rolesql}
                    )";
            $params = $params + $roleparams;
        }

        $sql .= " ORDER BY l.timecreated ASC, l.userid ASC";

        return $DB->get_recordset_sql($sql, $params);
    }

    protected function get_student_role_ids() {
        global $DB;

        $roleids = $DB->get_fieldset_select(
            'role',
            'id',
            'archetype = :studentarchetype OR shortname = :studentshortname',
            array(
                'studentarchetype' => 'student',
                'studentshortname' => 'student',
            )
        );

        return array_values(array_unique(array_map('intval', $roleids)));
    }
}
