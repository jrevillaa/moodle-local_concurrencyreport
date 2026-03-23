<?php
// This file is part of Moodle - http://moodle.org/

namespace local_concurrencyreport\local;

defined('MOODLE_INTERNAL') || die();

class bucket_factory {
    public function build($from, $to, $granularity) {
        $buckets = array();
        $cursor = $this->align_timestamp($from, $granularity);

        while ($cursor <= $to) {
            $buckets = $this->append_bucket($buckets, $cursor, $from, $granularity);
            $cursor = $this->next_timestamp($cursor, $granularity);
        }

        return $buckets;
    }

    public function align_timestamp($timestamp, $granularity) {
        $parts = $this->get_date_parts($timestamp);

        switch ($granularity) {
            case 'minute':
                return $timestamp - ($timestamp % MINSECS);
            case 'hour':
                return $timestamp - ($timestamp % HOURSECS);
            case 'day':
                return make_timestamp($parts['year'], $parts['month'], $parts['day'], 0, 0, 0);
            case 'month':
                return make_timestamp($parts['year'], $parts['month'], 1, 0, 0, 0);
            case 'year':
                return make_timestamp($parts['year'], 1, 1, 0, 0, 0);
            case 'second':
            default:
                return $timestamp;
        }
    }

    public function format_label($timestamp, $granularity) {
        $formats = array(
            'second' => '%Y-%m-%d %H:%M:%S',
            'minute' => '%Y-%m-%d %H:%M',
            'hour' => '%Y-%m-%d %H:00',
            'day' => '%Y-%m-%d',
            'month' => '%Y-%m',
            'year' => '%Y',
        );

        $format = !empty($formats[$granularity]) ? $formats[$granularity] : '%Y-%m-%d %H:%M:%S';
        return userdate($timestamp, $format);
    }

    protected function append_bucket(array $buckets, $cursor, $from, $granularity) {
        if ($cursor < $from) {
            return $buckets;
        }

        $buckets[] = array(
            'timestamp' => $cursor,
            'label' => $this->format_label($cursor, $granularity),
        );

        return $buckets;
    }

    protected function next_timestamp($timestamp, $granularity) {
        switch ($granularity) {
            case 'second':
                return $timestamp + 1;
            case 'minute':
                return $timestamp + MINSECS;
            case 'hour':
                return $timestamp + HOURSECS;
            case 'day':
                return $timestamp + DAYSECS;
            case 'month':
                return strtotime('+1 month', $timestamp);
            case 'year':
                return strtotime('+1 year', $timestamp);
            default:
                return $timestamp + MINSECS;
        }
    }

    protected function get_date_parts($timestamp) {
        return array(
            'year' => (int)userdate($timestamp, '%Y'),
            'month' => (int)userdate($timestamp, '%m'),
            'day' => (int)userdate($timestamp, '%d'),
        );
    }
}
