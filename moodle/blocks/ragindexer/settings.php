<?php
defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {

    $settings->add(new admin_setting_heading(
        'block_ragindexer/connectionheading',
        get_string('settings', 'block_ragindexer'),
        ''
    ));

    // URL del backend de indexación.
    $settings->add(new admin_setting_configtext(
        'block_ragindexer/apiurl',
        get_string('apiurl', 'block_ragindexer'),
        get_string('apiurl_desc', 'block_ragindexer'),
        'http://localhost:8000',
        PARAM_URL
    ));

    // Token de autenticación.
    $settings->add(new admin_setting_configpasswordunmask(
        'block_ragindexer/apikey',
        get_string('apikey', 'block_ragindexer'),
        get_string('apikey_desc', 'block_ragindexer'),
        ''
    ));

    // Timeout.
    $settings->add(new admin_setting_configtext(
        'block_ragindexer/timeout',
        get_string('timeout', 'block_ragindexer'),
        get_string('timeout_desc', 'block_ragindexer'),
        '30',
        PARAM_INT
    ));
}
