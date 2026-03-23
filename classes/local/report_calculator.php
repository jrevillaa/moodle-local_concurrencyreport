<?php
// This file is part of Moodle - http://moodle.org/

namespace local_concurrencyreport\local;

defined('MOODLE_INTERNAL') || die();

class report_calculator {
    public function calculate(array $buckets, \Traversable $recordset, $window) {
        $state = array(
            'latest' => array(),
            'rows' => array(),
            'current' => $this->read_current($recordset),
        );
        $heap = new active_user_heap();

        foreach ($buckets as $bucket) {
            $state = $this->consume_bucket($state, $bucket, $recordset, $heap, (int)$window);
        }

        if (method_exists($recordset, 'close')) {
            $recordset->close();
        }

        return $state['rows'];
    }

    protected function consume_bucket(array $state, array $bucket, \Traversable $recordset, active_user_heap $heap, $window) {
        $state = $this->register_bucket_events($state, $bucket, $recordset, $heap, $window);
        $state['latest'] = $this->purge_expired($heap, $state['latest'], $bucket['timestamp'], $window);
        $state['rows'][] = array(
            'timestamp' => $bucket['timestamp'],
            'label' => $bucket['label'],
            'value' => count($state['latest']),
        );

        return $state;
    }

    protected function register_bucket_events(array $state, array $bucket, \Traversable $recordset, active_user_heap $heap, $window) {
        while (!empty($state['current']) && (int)$state['current']->timecreated <= (int)$bucket['timestamp']) {
            $userid = (int)$state['current']->userid;
            $eventtime = (int)$state['current']->timecreated;

            $state['latest'][$userid] = $eventtime;
            $heap->insert(array(
                'expires' => $eventtime + $window,
                'userid' => $userid,
            ));
            $state['current'] = $this->advance_recordset($recordset);
        }

        return $state;
    }

    protected function purge_expired(active_user_heap $heap, array $latest, $buckettimestamp, $window) {
        while (!$heap->isEmpty() && $this->has_expired($heap->top(), $buckettimestamp)) {
            $entry = $heap->extract();
            $userid = $entry['userid'];
            $iscurrent = isset($latest[$userid]) && ((int)$latest[$userid] + $window) === (int)$entry['expires'];

            if ($iscurrent) {
                unset($latest[$userid]);
            }
        }

        return $latest;
    }

    protected function has_expired(array $entry, $buckettimestamp) {
        return (int)$entry['expires'] < (int)$buckettimestamp;
    }

    protected function read_current(\Traversable $recordset) {
        if (!method_exists($recordset, 'valid') || !method_exists($recordset, 'current')) {
            return null;
        }

        return $recordset->valid() ? $recordset->current() : null;
    }

    protected function advance_recordset(\Traversable $recordset) {
        if (!method_exists($recordset, 'next') || !method_exists($recordset, 'valid') || !method_exists($recordset, 'current')) {
            return null;
        }

        $recordset->next();

        return $recordset->valid() ? $recordset->current() : null;
    }
}
