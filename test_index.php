<?php

require __DIR__ . '/common.php';

$response = $otsClient->describeSearchIndex(array(
    'table_name' => 'test_index',
    'index_name' => 'test_index_index'
));

print json_encode($response, JSON_PRETTY_PRINT);
