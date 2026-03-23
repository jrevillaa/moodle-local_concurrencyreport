<?php
// This file is part of Moodle - http://moodle.org/

namespace local_concurrencyreport\output;

defined('MOODLE_INTERNAL') || die();

class report_page implements \renderable, \templatable {
    const DEFAULT_ROWS_PER_PAGE = 250;

    protected $form;
    protected $filters;
    protected $report;

    public function __construct(\moodleform $form, array $filters, array $report) {
        $this->form = $form;
        $this->filters = $filters;
        $this->report = $report;
    }

    public function export_for_template(\renderer_base $output) {
        $pagination = $this->build_pagination();

        return array(
            'title' => get_string('pluginname', 'local_concurrencyreport'),
            'definition' => get_string('definition', 'local_concurrencyreport'),
            'formhtml' => $this->render_form(),
            'errors' => $this->export_errors(),
            'hasresults' => !empty($this->report['rows']),
            'rows' => $pagination['rows'],
            'summaryline' => get_string('summaryline', 'local_concurrencyreport', (object)$this->report['summary']),
            'recordsline' => get_string('recordsline', 'local_concurrencyreport', count($this->report['rows'])),
            'exporturls' => $this->export_urls(),
            'bucketlabel' => get_string('bucket', 'local_concurrencyreport'),
            'concurrentuserslabel' => get_string('concurrentusers', 'local_concurrencyreport'),
            'haspagination' => $pagination['totalpages'] > 1,
            'pagingline' => get_string('pagexofy', 'local_concurrencyreport', (object)array(
                'current' => $pagination['currentpage'] + 1,
                'total' => $pagination['totalpages'],
            )),
            'previouspageurl' => $pagination['previouspageurl'],
            'nextpageurl' => $pagination['nextpageurl'],
            'haspreviouspage' => $pagination['previouspageurl'] !== null,
            'hasnextpage' => $pagination['nextpageurl'] !== null,
            'previouspagelabel' => get_string('previouspage', 'local_concurrencyreport'),
            'nextpagelabel' => get_string('nextpage', 'local_concurrencyreport'),
        );
    }

    protected function render_form() {
        ob_start();
        $this->form->display();
        return ob_get_clean();
    }

    protected function export_errors() {
        return array_map(array($this, 'map_error'), $this->report['errors']);
    }

    protected function map_error($message) {
        return array('message' => $message);
    }

    protected function export_rows() {
        return array_map(array($this, 'map_row'), $this->report['rows']);
    }

    protected function build_pagination() {
        $rows = $this->export_rows();
        $perpage = $this->get_rows_per_page();
        $totalrows = count($rows);
        $totalpages = max(1, (int)ceil($totalrows / $perpage));
        $currentpage = min($this->get_current_page(), $totalpages - 1);
        $offset = $currentpage * $perpage;

        return array(
            'rows' => array_slice($rows, $offset, $perpage),
            'currentpage' => $currentpage,
            'totalpages' => $totalpages,
            'previouspageurl' => $currentpage > 0 ? $this->build_page_url($currentpage - 1) : null,
            'nextpageurl' => $currentpage < ($totalpages - 1) ? $this->build_page_url($currentpage + 1) : null,
        );
    }

    protected function map_row(array $row) {
        return array(
            'label' => $row['label'],
            'value' => $row['value'],
        );
    }

    protected function export_urls() {
        $baseparams = array(
            'run' => 1,
            'from' => $this->filters['from'],
            'to' => $this->filters['to'],
            'granularity' => $this->filters['granularity'],
            'window' => $this->filters['window'],
            'audience' => $this->filters['audience'],
        );

        return array(
            array(
                'label' => get_string('downloadcsv', 'local_concurrencyreport'),
                'url' => (new \moodle_url('/local/concurrencyreport/index.php', $baseparams + array('download' => 'csv')))->out(false),
            ),
            array(
                'label' => get_string('downloadxlsx', 'local_concurrencyreport'),
                'url' => (new \moodle_url('/local/concurrencyreport/index.php', $baseparams + array('download' => 'xlsx')))->out(false),
            ),
        );
    }

    protected function build_page_url($page) {
        return (new \moodle_url('/local/concurrencyreport/index.php', array(
            'run' => 1,
            'from' => $this->filters['from'],
            'to' => $this->filters['to'],
            'granularity' => $this->filters['granularity'],
            'window' => $this->filters['window'],
            'audience' => $this->filters['audience'],
            'page' => max(0, (int)$page),
        )))->out(false);
    }

    protected function get_current_page() {
        return max(0, optional_param('page', 0, PARAM_INT));
    }

    protected function get_rows_per_page() {
        $configured = (int)get_config('local_concurrencyreport', 'rowsperpage');
        return $configured > 0 ? $configured : self::DEFAULT_ROWS_PER_PAGE;
    }
}
