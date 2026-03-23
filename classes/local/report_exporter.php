<?php
// This file is part of Moodle - http://moodle.org/

namespace local_concurrencyreport\local;

defined('MOODLE_INTERNAL') || die();

class report_exporter {
    public function maybe_download(array $rows, array $filters) {
        $format = optional_param('download', '', PARAM_ALPHA);

        if (!$this->should_download($format)) {
            return;
        }

        $columns = array(
            'bucket' => get_string('bucket', 'local_concurrencyreport'),
            'concurrentusers' => get_string('concurrentusers', 'local_concurrencyreport'),
        );

        \core\dataformat::download_data(
            $this->build_filename($filters),
            $format,
            $columns,
            new \ArrayIterator($rows),
            array($this, 'format_export_row')
        );
        exit;
    }

    protected function should_download($format) {
        return in_array($format, array('csv', 'xlsx'), true);
    }

    public function format_export_row($row, $supportshtml) {
        return array(
            'bucket' => $row['label'],
            'concurrentusers' => $row['value'],
        );
    }

    protected function build_filename(array $filters) {
        return implode('_', array(
            'concurrency',
            $filters['granularity'],
            userdate($filters['from'], '%Y%m%d_%H%M%S'),
            userdate($filters['to'], '%Y%m%d_%H%M%S'),
        ));
    }
}
