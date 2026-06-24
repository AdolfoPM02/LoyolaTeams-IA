<?php
defined('MOODLE_INTERNAL') || die();

$functions = [

    'block_ragassistant_ask' => [
        'classname'   => 'block_ragassistant\external\ask',
        'methodname'  => 'execute',
        'description' => 'Send a question to the RAG backend',
        'type'        => 'read',
        'ajax'        => true,
        'capabilities'=> 'block/ragassistant:ask',
    ],
];
