<?php
// This file is part of Moodle - http://moodle.org/

namespace local_concurrencyreport\local;

defined('MOODLE_INTERNAL') || die();

class filter_manager {
    const AUDIENCE_ALL = 'all';
    const AUDIENCE_STUDENTS = 'students';

    public function get_default_filters() {
        $now = time();

        return array(
            'from' => $now - DAYSECS,
            'to' => $now,
            'granularity' => 'minute',
            'window' => $this->get_default_window(),
            'audience' => self::AUDIENCE_STUDENTS,
        );
    }

    public function get_granularity_options() {
        return array(
            'second' => get_string('granularity_second', 'local_concurrencyreport'),
            'minute' => get_string('granularity_minute', 'local_concurrencyreport'),
            'hour' => get_string('granularity_hour', 'local_concurrencyreport'),
            'day' => get_string('granularity_day', 'local_concurrencyreport'),
            'month' => get_string('granularity_month', 'local_concurrencyreport'),
            'year' => get_string('granularity_year', 'local_concurrencyreport'),
        );
    }

    public function get_audience_options() {
        return array(
            self::AUDIENCE_STUDENTS => get_string('audience_students', 'local_concurrencyreport'),
            self::AUDIENCE_ALL => get_string('audience_all', 'local_concurrencyreport'),
        );
    }

    public function resolve_submitted_filters(\moodleform $form) {
        $defaults = $this->get_default_filters();
        $data = $form->get_data();

        if (!empty($data)) {
            return $this->normalize_filters((array)$data, $defaults);
        }

        return $this->normalize_filters(array(
            'from' => optional_param('from', $defaults['from'], PARAM_INT),
            'to' => optional_param('to', $defaults['to'], PARAM_INT),
            'granularity' => optional_param('granularity', $defaults['granularity'], PARAM_ALPHA),
            'window' => optional_param('window', $defaults['window'], PARAM_INT),
            'audience' => optional_param('audience', $defaults['audience'], PARAM_ALPHA),
        ), $defaults);
    }

    public function should_run_report(\moodleform $form) {
        return $form->is_submitted() || (bool)optional_param('run', 0, PARAM_BOOL);
    }

    public function get_default_window() {
        $configured = (int)get_config('local_concurrencyreport', 'activitywindow');
        return $configured > 0 ? $configured : 300;
    }

    protected function normalize_filters(array $filters, array $defaults) {
        return array(
            'from' => !empty($filters['from']) ? (int)$filters['from'] : $defaults['from'],
            'to' => !empty($filters['to']) ? (int)$filters['to'] : $defaults['to'],
            'granularity' => !empty($filters['granularity']) ? (string)$filters['granularity'] : $defaults['granularity'],
            'window' => !empty($filters['window']) ? (int)$filters['window'] : $defaults['window'],
            'audience' => !empty($filters['audience']) ? (string)$filters['audience'] : $defaults['audience'],
        );
    }
}
