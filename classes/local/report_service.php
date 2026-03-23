<?php
// This file is part of Moodle - http://moodle.org/

namespace local_concurrencyreport\local;

defined('MOODLE_INTERNAL') || die();

class report_service {
    protected $filtermanager;
    protected $rangelimiter;
    protected $bucketfactory;
    protected $logrepository;
    protected $calculator;
    protected $summarybuilder;

    public function __construct(
        $filtermanager = null,
        $rangelimiter = null,
        $bucketfactory = null,
        $logrepository = null,
        $calculator = null,
        $summarybuilder = null
    ) {
        $this->filtermanager = $filtermanager ?: new filter_manager();
        $this->rangelimiter = $rangelimiter ?: new range_limiter();
        $this->bucketfactory = $bucketfactory ?: new bucket_factory();
        $this->logrepository = $logrepository ?: new log_repository();
        $this->calculator = $calculator ?: new report_calculator();
        $this->summarybuilder = $summarybuilder ?: new summary_builder();
    }

    public function get_filter_manager() {
        return $this->filtermanager;
    }

    public function build_report(array $filters) {
        $errors = $this->validate_filters($filters);

        if (!empty($errors)) {
            return array('rows' => array(), 'summary' => $this->summarybuilder->build(array()), 'errors' => $errors);
        }

        $buckets = $this->bucketfactory->build($filters['from'], $filters['to'], $filters['granularity']);
        $recordset = $this->logrepository->get_activity_recordset($filters);
        $rows = $this->calculator->calculate($buckets, $recordset, $filters['window']);

        return array(
            'rows' => $rows,
            'summary' => $this->summarybuilder->build($rows),
            'errors' => array(),
        );
    }

    protected function validate_filters(array $filters) {
        $errors = array();

        if ((int)$filters['to'] <= (int)$filters['from']) {
            $errors[] = get_string('errorinvalidrange', 'local_concurrencyreport');
        }

        if ((int)$filters['window'] < 1) {
            $errors[] = get_string('errorinvalidwindow', 'local_concurrencyreport');
        }

        if (empty($this->filtermanager->get_granularity_options()[$filters['granularity']])) {
            $errors[] = get_string('errorinvalidgranularity', 'local_concurrencyreport');
        }

        if (empty($this->filtermanager->get_audience_options()[$filters['audience']])) {
            $errors[] = get_string('errorinvalidaudience', 'local_concurrencyreport');
        }

        if (!$this->rangelimiter->is_allowed($filters['from'], $filters['to'], $filters['granularity'])) {
            $errors[] = get_string('errorrangetoolarge', 'local_concurrencyreport');
        }

        return $errors;
    }
}
