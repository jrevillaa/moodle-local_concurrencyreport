<?php
// This file is part of Moodle - http://moodle.org/

namespace local_concurrencyreport\local;

defined('MOODLE_INTERNAL') || die();

class active_user_heap extends \SplMinHeap {
    protected function compare($value1, $value2): int {
        if ($value1['expires'] === $value2['expires']) {
            if ($value1['userid'] === $value2['userid']) {
                return 0;
            }

            return $value1['userid'] < $value2['userid'] ? -1 : 1;
        }

        return $value1['expires'] < $value2['expires'] ? -1 : 1;
    }
}
