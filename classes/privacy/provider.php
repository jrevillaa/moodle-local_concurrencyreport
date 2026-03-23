<?php
// This file is part of Moodle - http://moodle.org/

namespace local_concurrencyreport\privacy;

defined('MOODLE_INTERNAL') || die();

class provider implements
    \core_privacy\local\metadata\null_provider {

    public static function get_reason() {
        return 'privacy:metadata';
    }
}
