<?php
// This file is part of Moodle - http://moodle.org/

namespace local_concurrencyreport;

defined('MOODLE_INTERNAL') || die();

class report_service_test extends \advanced_testcase {
    public function test_bucket_factory_builds_minute_labels() {
        $factory = new \local_concurrencyreport\local\bucket_factory();
        $from = make_timestamp(2026, 3, 20, 10, 1, 5);
        $to = make_timestamp(2026, 3, 20, 10, 3, 5);

        $buckets = $factory->build($from, $to, 'minute');

        $this->assertCount(3, $buckets);
        $this->assertSame('2026-03-20 10:02', $buckets[1]['label']);
    }

    public function test_summary_builder_calculates_aggregates() {
        $builder = new \local_concurrencyreport\local\summary_builder();
        $summary = $builder->build(array(
            array('value' => 2),
            array('value' => 4),
            array('value' => 6),
        ));

        $this->assertSame(6, $summary['max']);
        $this->assertSame(4.0, $summary['avg']);
        $this->assertSame(2, $summary['min']);
    }

    public function test_report_service_rejects_invalid_range() {
        $service = new \local_concurrencyreport\local\report_service(
            new \local_concurrencyreport\local\filter_manager(),
            new \local_concurrencyreport\local\range_limiter(),
            new \local_concurrencyreport\local\bucket_factory(),
            new fake_log_repository(new \ArrayIterator(array())),
            new \local_concurrencyreport\local\report_calculator(),
            new \local_concurrencyreport\local\summary_builder()
        );

        $report = $service->build_report(array(
            'from' => 200,
            'to' => 100,
            'granularity' => 'minute',
            'window' => 300,
            'audience' => \local_concurrencyreport\local\filter_manager::AUDIENCE_STUDENTS,
        ));

        $this->assertNotEmpty($report['errors']);
    }

    public function test_report_calculator_counts_active_users() {
        $calculator = new \local_concurrencyreport\local\report_calculator();
        $buckets = array(
            array('timestamp' => 100, 'label' => 'A'),
            array('timestamp' => 200, 'label' => 'B'),
            array('timestamp' => 350, 'label' => 'C'),
        );
        $recordset = new \ArrayIterator(array(
            (object)array('userid' => 10, 'timecreated' => 100),
            (object)array('userid' => 20, 'timecreated' => 120),
            (object)array('userid' => 10, 'timecreated' => 340),
        ));

        $rows = $calculator->calculate($buckets, $recordset, 120);

        $this->assertSame(1, $rows[0]['value']);
        $this->assertSame(2, $rows[1]['value']);
        $this->assertSame(1, $rows[2]['value']);
    }

    public function test_page_controller_skips_export_when_report_has_errors() {
        $service = new fake_report_service(array(
            'from' => 100,
            'to' => 200,
            'granularity' => 'minute',
            'window' => 300,
            'audience' => \local_concurrencyreport\local\filter_manager::AUDIENCE_STUDENTS,
        ), array(
            'rows' => array(),
            'summary' => array('max' => 0, 'avg' => 0, 'min' => 0),
            'errors' => array('Invalid range'),
        ));
        $exporter = new fake_report_exporter();

        $controller = new \local_concurrencyreport\local\page_controller($service, $exporter);
        $page = $controller->build_page();

        $this->assertInstanceOf(\local_concurrencyreport\output\report_page::class, $page);
        $this->assertFalse($exporter->called);
    }

    public function test_report_page_paginates_rows() {
        set_config('rowsperpage', 2, 'local_concurrencyreport');

        $form = $this->createMock(\moodleform::class);
        $form->method('display')->willReturnCallback(function() {
        });

        $page = new \local_concurrencyreport\output\report_page($form, array(
            'from' => 100,
            'to' => 200,
            'granularity' => 'minute',
            'window' => 300,
            'audience' => \local_concurrencyreport\local\filter_manager::AUDIENCE_STUDENTS,
        ), array(
            'rows' => array(
                array('label' => 'A', 'value' => 1),
                array('label' => 'B', 'value' => 2),
                array('label' => 'C', 'value' => 3),
            ),
            'summary' => array('max' => 3, 'avg' => 2, 'min' => 1),
            'errors' => array(),
        ));

        $data = $page->export_for_template($this->createMock(\renderer_base::class));

        $this->assertTrue($data['haspagination']);
        $this->assertCount(2, $data['rows']);
        $this->assertSame('A', $data['rows'][0]['label']);
        $this->assertSame('B', $data['rows'][1]['label']);
        $this->assertNotEmpty($data['nextpageurl']);
    }
}

class fake_log_repository extends \local_concurrencyreport\local\log_repository {
    protected $recordset;

    public function __construct(\Traversable $recordset) {
        $this->recordset = $recordset;
    }

    public function get_activity_recordset(array $filters) {
        return $this->recordset;
    }
}

class fake_report_service extends \local_concurrencyreport\local\report_service {
    protected $filters;
    protected $report;
    protected $filtermanager;

    public function __construct(array $filters, array $report) {
        $this->filters = $filters;
        $this->report = $report;
        $this->filtermanager = new fake_filter_manager($filters);
    }

    public function get_filter_manager() {
        return $this->filtermanager;
    }

    public function build_report(array $filters) {
        return $this->report;
    }
}

class fake_filter_manager extends \local_concurrencyreport\local\filter_manager {
    protected $filters;

    public function __construct(array $filters) {
        $this->filters = $filters;
    }

    public function resolve_submitted_filters(\moodleform $form) {
        return $this->filters;
    }

    public function should_run_report(\moodleform $form) {
        return true;
    }
}

class fake_report_exporter extends \local_concurrencyreport\local\report_exporter {
    public $called = false;

    public function maybe_download(array $rows, array $filters) {
        $this->called = true;
    }
}
