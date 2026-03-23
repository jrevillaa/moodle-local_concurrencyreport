<?php
// This file is part of Moodle - http://moodle.org/

defined('MOODLE_INTERNAL') || die();

class local_concurrencyreport_renderer extends plugin_renderer_base {
    public function render_report_page(\local_concurrencyreport\output\report_page $page) {
        return $this->render_from_template('local_concurrencyreport/report_page', $page->export_for_template($this));
    }
}
