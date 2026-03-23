<?php
// This file is part of Moodle - http://moodle.org/

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $ADMIN->add('reports', new admin_externalpage(
        'local_concurrencyreport',
        get_string('pluginname', 'local_concurrencyreport'),
        new moodle_url('/local/concurrencyreport/index.php'),
        'local/concurrencyreport:view'
    ));

    $settings = new admin_settingpage(
        'local_concurrencyreport_settings',
        get_string('settingsheading', 'local_concurrencyreport')
    );

    $settings->add(new admin_setting_configtext(
        'local_concurrencyreport/activitywindow',
        get_string('activitywindow', 'local_concurrencyreport'),
        get_string('activitywindow_desc', 'local_concurrencyreport'),
        300,
        PARAM_INT
    ));

    $settings->add(new admin_setting_configtext(
        'local_concurrencyreport/maxsecondsrange',
        get_string('maxsecondsrange', 'local_concurrencyreport'),
        get_string('maxsecondsrange_desc', 'local_concurrencyreport'),
        86400,
        PARAM_INT
    ));

    $settings->add(new admin_setting_configtext(
        'local_concurrencyreport/maxminutesrange',
        get_string('maxminutesrange', 'local_concurrencyreport'),
        get_string('maxminutesrange_desc', 'local_concurrencyreport'),
        2678400,
        PARAM_INT
    ));

    $settings->add(new admin_setting_configtext(
        'local_concurrencyreport/maxhoursrange',
        get_string('maxhoursrange', 'local_concurrencyreport'),
        get_string('maxhoursrange_desc', 'local_concurrencyreport'),
        31622400,
        PARAM_INT
    ));

    $settings->add(new admin_setting_configtext(
        'local_concurrencyreport/maxdaysrange',
        get_string('maxdaysrange', 'local_concurrencyreport'),
        get_string('maxdaysrange_desc', 'local_concurrencyreport'),
        315576000,
        PARAM_INT
    ));

    $settings->add(new admin_setting_configtext(
        'local_concurrencyreport/rowsperpage',
        get_string('rowsperpage', 'local_concurrencyreport'),
        get_string('rowsperpage_desc', 'local_concurrencyreport'),
        250,
        PARAM_INT
    ));

    $ADMIN->add('localplugins', $settings);
}
