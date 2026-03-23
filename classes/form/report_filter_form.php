<?php
// This file is part of Moodle - http://moodle.org/

namespace local_concurrencyreport\form;

defined('MOODLE_INTERNAL') || die();

require_once($GLOBALS['CFG']->libdir . '/formslib.php');

class report_filter_form extends \moodleform {
    public function definition() {
        $mform = $this->_form;
        $options = $this->_customdata;

        $mform->addElement('date_time_selector', 'from', get_string('from'));
        $mform->addRule('from', null, 'required', null, 'client');

        $mform->addElement('date_time_selector', 'to', get_string('to'));
        $mform->addRule('to', null, 'required', null, 'client');

        $mform->addElement(
            'select',
            'granularity',
            get_string('granularity', 'local_concurrencyreport'),
            $options['granularityoptions']
        );
        $mform->setType('granularity', PARAM_ALPHA);

        $mform->addElement('text', 'window', get_string('activitywindow', 'local_concurrencyreport'));
        $mform->setType('window', PARAM_INT);
        $mform->addHelpButton('window', 'activitywindow', 'local_concurrencyreport');

        $mform->addElement(
            'select',
            'audience',
            get_string('audience', 'local_concurrencyreport'),
            $options['audienceoptions']
        );
        $mform->setType('audience', PARAM_ALPHA);

        $mform->addElement('hidden', 'run', 1);
        $mform->setType('run', PARAM_BOOL);

        $this->add_action_buttons(false, get_string('generatereport', 'local_concurrencyreport'));
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        $to = (int)$data['to'];
        $from = (int)$data['from'];
        $window = (int)$data['window'];

        if ($to <= $from) {
            $errors['to'] = get_string('errorinvalidrange', 'local_concurrencyreport');
        }

        if ($window < 1) {
            $errors['window'] = get_string('errorinvalidwindow', 'local_concurrencyreport');
        }

        return $errors;
    }
}
