<?php
defined('MOODLE_INTERNAL') || die();

$string['pluginname']                = 'RAG Indexer';
$string['ragindexer:addinstance']    = 'Add RAG Indexer block';
$string['ragindexer:indexcourse']    = 'Index course materials';
$string['ragindexer:forcereindex']   = 'Force full reindex';

// Admin settings.
$string['settings']                  = 'RAG Indexer settings';
$string['apiurl']                    = 'Indexing backend URL';
$string['apiurl_desc']               = 'Base URL of the indexing backend.';
$string['apikey']                    = 'Auth token';
$string['apikey_desc']               = 'Bearer token to authenticate requests to the indexing backend.';
$string['timeout']                   = 'Timeout (seconds)';
$string['timeout_desc']              = 'Maximum seconds to wait for the indexing backend response.';

// Block interface.
$string['backendpending']            = 'Course indexing depends on a backend that may not be available yet.';
$string['indexbutton']               = 'Update course index';
$string['indexing']                  = 'Indexing materials...';
$string['indexok']                   = 'Index updated successfully.';
$string['indexerror']                = 'Error updating the index. The indexing backend may not be available yet.';

// Internal errors.
$string['invalidapiresponse']        = 'Invalid response from the indexing backend.';
$string['apiurlnotset']              = 'Indexing backend URL not configured. Contact your administrator.';
