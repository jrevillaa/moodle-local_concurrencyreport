<?php
// This file is part of Moodle - http://moodle.org/

namespace local_concurrencyreport\local;

defined('MOODLE_INTERNAL') || die();

class page_controller {
    protected $service;
    protected $exporter;

    public function __construct($service = null, $exporter = null) {
        $this->service = $service ?: new report_service();
        $this->exporter = $exporter ?: new report_exporter();
    }

    public function build_page() {
        $filtermanager = $this->service->get_filter_manager();
        $form = new \local_concurrencyreport\form\report_filter_form(
            new \moodle_url('/local/concurrencyreport/index.php'),
            array(
                'granularityoptions' => $filtermanager->get_granularity_options(),
                'audienceoptions' => $filtermanager->get_audience_options(),
            ),
            'get'
        );

        $filters = $filtermanager->resolve_submitted_filters($form);
        $form->set_data((object)$filters);

        $report = array('rows' => array(), 'summary' => array('max' => 0, 'avg' => 0, 'min' => 0), 'errors' => array());

        if ($filtermanager->should_run_report($form)) {
            $report = $this->service->build_report($filters);

            if (empty($report['errors'])) {
                $this->exporter->maybe_download($report['rows'], $filters);
            }
        }

        return new \local_concurrencyreport\output\report_page($form, $filters, $report);
    }
}
