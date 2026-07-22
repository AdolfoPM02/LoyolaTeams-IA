<?php
defined('MOODLE_INTERNAL') || die();

$string['pluginname']                = 'RAG Indexer';
$string['ragindexer:addinstance']    = 'Add RAG Indexer block';
$string['ragindexer:indexcourse']    = 'Index course materials';
$string['ragindexer:forcereindex']   = 'Force full reindex';

// Admin settings.
$string['settings']                  = 'RAG Indexer settings';
$string['apiurl']                    = 'Indexing backend URL';
$string['apiurl_desc']               = 'Base URL of the FastAPI indexing backend, e.g. http://host.docker.internal:8003';
$string['apikey']                    = 'Indexer token';
$string['apikey_desc']               = 'Bearer token to authenticate requests to the indexing backend (stored hidden).';
$string['timeout']                   = 'Timeout (seconds)';
$string['timeout_desc']              = 'Maximum seconds to wait for the indexing backend response.';

// Block interface.
$string['indexbutton']               = 'Update course index';
$string['forcereindex']              = 'Force full reindex';
$string['indexing']                  = 'Indexing materials...';
$string['indexerror']                = 'Error updating the index. Please try again.';
$string['indexpartialnotice']        = 'Indexing completed partially. Some resources could not be processed.';
$string['errorsheading']             = 'Issues';

// Result statuses.
$string['statusok']                  = 'Course indexed successfully.';
$string['statuspartial']             = 'Course indexed partially.';
$string['statusfailed']              = 'Indexing failed.';

// Result counters.
$string['resdocsnew']                = 'New documents';
$string['resdocsmodified']           = 'Modified documents';
$string['resdocsdeleted']            = 'Deleted documents';
$string['reschunksadded']            = 'Chunks added';
$string['reschunksstale']            = 'Chunks deleted/stale';
$string['resembeddings']             = 'Embeddings recomputed';
$string['resvisibility']             = 'Visibility updates';

// Transport / internal errors (no traces, no token).
$string['backendunavailable']        = 'The indexing backend is not available right now. Please try again later.';
$string['errorinvalidrequest']       = 'The indexing request was rejected. Contact your administrator.';
$string['errorunauthorized']         = 'The indexing backend rejected authentication. Contact your administrator.';
$string['errorinternal']             = 'The indexing backend reported an internal error. Please try again later.';
$string['invalidapiresponse']        = 'Invalid response from the indexing backend.';
$string['apiurlnotset']              = 'Indexing backend URL not configured. Contact your administrator.';
$string['tokennotset']               = 'Indexer token not configured. Contact your administrator.';
