<?php
defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {

    $settings->add(new admin_setting_heading(
        'block_ragassistant/connectionheading',
        get_string('settings', 'block_ragassistant'),
        ''
    ));

    // URL de FastAPI
    $settings->add(new admin_setting_configtext(
        'block_ragassistant/apiurl',
        get_string('apiurl', 'block_ragassistant'),
        get_string('apiurl_desc', 'block_ragassistant'),
        'http://localhost:8000',
        PARAM_URL
    ));

    // Token de autenticación
    $settings->add(new admin_setting_configpasswordunmask(
        'block_ragassistant/apikey',
        get_string('apikey', 'block_ragassistant'),
        get_string('apikey_desc', 'block_ragassistant'),
        ''
    ));

    // Timeout
    $settings->add(new admin_setting_configtext(
        'block_ragassistant/timeout',
        get_string('timeout', 'block_ragassistant'),
        get_string('timeout_desc', 'block_ragassistant'),
        '60',
        PARAM_INT
    ));

    // Top K
    $settings->add(new admin_setting_configtext(
        'block_ragassistant/topk',
        get_string('topk', 'block_ragassistant'),
        get_string('topk_desc', 'block_ragassistant'),
        '5',
        PARAM_INT
    ));

    $settings->add(new admin_setting_heading(
        'block_ragassistant/displayheading',
        'Visualización',
        ''
    ));

    // Mostrar fuentes
    $settings->add(new admin_setting_configcheckbox(
        'block_ragassistant/showsources',
        get_string('showsources', 'block_ragassistant'),
        get_string('showsources_desc', 'block_ragassistant'),
        1
    ));

    // Mostrar confianza
    $settings->add(new admin_setting_configcheckbox(
        'block_ragassistant/showconfidence',
        get_string('showconfidence', 'block_ragassistant'),
        get_string('showconfidence_desc', 'block_ragassistant'),
        0
    ));

    // Permitir indexación por profesores
    $settings->add(new admin_setting_configcheckbox(
        'block_ragassistant/allowindexing',
        get_string('allowindexing', 'block_ragassistant'),
        get_string('allowindexing_desc', 'block_ragassistant'),
        1
    ));

    // Modo debug
    $settings->add(new admin_setting_configcheckbox(
        'block_ragassistant/debugmode',
        get_string('debugmode', 'block_ragassistant'),
        get_string('debugmode_desc', 'block_ragassistant'),
        0
    ));
}
