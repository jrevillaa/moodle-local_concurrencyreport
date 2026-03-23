<?php
// This file is part of Moodle - http://moodle.org/

namespace local_concurrencyreport\local;

defined('MOODLE_INTERNAL') || die();

class summary_builder {
    public function build(array $rows) {
        if (empty($rows)) {
            return array('max' => 0, 'avg' => 0, 'min' => 0);
        }

        $values = array_map(array($this, 'extract_value'), $rows);

        return array(
            'max' => max($values),
            'avg' => round(array_sum($values) / count($values), 2),
            'min' => min($values),
        );
    }

    protected function extract_value(array $row) {
        return (int)$row['value'];
    }
}
