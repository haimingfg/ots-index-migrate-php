<?php

require __DIR__ . '/common.php';


$table_name = 'test_index';
$index_name = 'test_index_index';
$new_index_name = $index_name . '_new';

$old_index = $otsClient->describeSearchIndex(array(
    'table_name' => $table_name,
    'index_name' => $index_name
));


if (isset($old_index['index_schema']['field_schemas']) && is_array($old_index['index_schema']['field_schemas'])) {
    $field_schemas = $old_index['index_schema']['field_schemas'];
    $request_schema = [
        'table_name' => $table_name,
        'index_name' => $new_index_name,
    ];
    $new_field_schemas = [];
    foreach ($field_schemas as $field) {
        $new_field_schemas[] = $field;
    }

    if (!empty($new_field_schemas)) {
        $request_schema['schema']['field_schemas'] = $new_field_schemas;
    }
    var_dump($request_schema);
    $response = $otsClient->createSearchIndex($request_schema);
    var_dump($response);
}