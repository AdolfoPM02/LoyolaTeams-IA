<?php
defined('MOODLE_INTERNAL') || die();

$functions = [

    'block_ragindexer_index_course' => [
        'classname'   => 'block_ragindexer\external\index_course',
        'methodname'  => 'execute',
        'description' => 'Request course indexing in the RAG backend',
        'type'        => 'write',
        'ajax'        => true,
        'capabilities'=> 'block/ragindexer:indexcourse',
    ],
];
