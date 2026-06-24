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

    'block_ragassistant_index_course' => [
        'classname'   => 'block_ragassistant\external\index_course',
        'methodname'  => 'execute',
        'description' => 'Request course indexing in the RAG backend',
        'type'        => 'write',
        'ajax'        => true,
        'capabilities'=> 'block/ragassistant:indexcourse',
    ],
];
