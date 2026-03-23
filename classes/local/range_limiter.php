<?php
// This file is part of Moodle - http://moodle.org/

namespace local_concurrencyreport\local;

defined('MOODLE_INTERNAL') || die();

class range_limiter {
    public function is_allowed($from, $to, $granularity) {
        $range = (int)$to - (int)$from;
        $maxrange = $this->get_max_range($granularity);

        return $range > 0 && $maxrange > 0 && $range <= $maxrange;
    }

    public function get_max_range($granularity) {
        $map = array(
            'second' => array('config' => 'maxsecondsrange', 'default' => DAYSECS),
            'minute' => array('config' => 'maxminutesrange', 'default' => 31 * DAYSECS),
            'hour' => array('config' => 'maxhoursrange', 'default' => 366 * DAYSECS),
            'day' => array('config' => 'maxdaysrange', 'default' => 3650 * DAYSECS),
            'month' => array('config' => '', 'default' => 15 * YEARSECS),
            'year' => array('config' => '', 'default' => 25 * YEARSECS),
        );

        if (empty($map[$granularity])) {
            return 0;
        }

        $configkey = $map[$granularity]['config'];
        $configured = $configkey !== '' ? (int)get_config('local_concurrencyreport', $configkey) : 0;

        return $configured > 0 ? $configured : $map[$granularity]['default'];
    }
}
