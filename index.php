<?php
// This file is part of Moodle - http://moodle.org/

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/dataformatlib.php');
require_once(__DIR__ . '/classes/form/report_filter_form.php');

admin_externalpage_setup('local_concurrencyreport');

$context = context_system::instance();
require_capability('local/concurrencyreport:view', $context);

$controller = new \local_concurrencyreport\local\page_controller();
$page = $controller->build_page();

$PAGE->set_url(new moodle_url('/local/concurrencyreport/index.php'));
$PAGE->set_context($context);
$PAGE->set_title(get_string('pluginname', 'local_concurrencyreport'));
$PAGE->set_heading(get_string('pluginname', 'local_concurrencyreport'));

echo $OUTPUT->header();
echo $OUTPUT->render($page);
echo $OUTPUT->footer();
