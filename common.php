<?php
require __DIR__ . '/vendor/autoload.php';

use Aliyun\OTS\OTSClient as OTSClient;
use Aliyun\OTS\Consts\FieldTypeConst;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

function obtain_ots_client()
{
    static $otsClient = null;

    if (!isset($otsClient)) {
        $otsClient = new OTSClient(array(
            'EndPoint' => $_ENV['OTS_ENDPOINT'],
            'AccessKeyID' => $_ENV['OTS_ACCESSKEYID'],
            'AccessKeySecret' => $_ENV['OTS_ACCESSKEYSECRET'],
            'InstanceName' => $_ENV['OTS_INSTANCENAME'],
        ));
    }
    
    return $otsClient;
}

function create_new_index_by_index_schemas(
    $table_name, $new_index_name, $old_index_name,
    $add_fields = [], $delete_fields =[]
)
{
    $otsClient = obtain_ots_client();

    $old_index = $otsClient->describeSearchIndex(array(
        'table_name' => $table_name,
        'index_name' => $old_index_name
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
        
        if (!empty($delete_fields)) {
            foreach ($new_field_schemas as $index => $new_field_schema) {
                $field = $new_field_schema['field_name'];
                // var_dump($delete_fields, $field);
                if (isset($delete_fields[$field])) {
                    unset($new_field_schemas[$index]);
                }
            }
        }

        if (!empty($add_fields)) {
            $new_field_schemas = array_merge($new_field_schemas, $add_fields);
        }

        
    
        if (!empty($new_field_schemas)) {
            $request_schema['schema']['field_schemas'] = $new_field_schemas;
        }
        // var_dump($request_schema);exit;
        $response = $otsClient->createSearchIndex($request_schema);
    }

    return true;
}


function check_sync_status($table_name, $new_index_name, $old_index_name)
{
    $otsClient = obtain_ots_client();
    $index_list = $otsClient->listSearchIndex(array(
        'table_name' => $table_name,
    ));
    
    if ($index_list) {
        $pass = true;
        foreach ($index_list as $index_info) {
            $index_name = $index_info['index_name'];
            if(in_array($index_name, [$new_index_name, $old_index_name])) {
                $pass = false;
            }
            if ($pass === false) break;
        }
        if ($pass === false) return false;
    }

    $old_index = $otsClient->describeSearchIndex(array(
        'table_name' => $table_name,
        'index_name' => $old_index_name
    ));

    $new_index = $otsClient->describeSearchIndex(array(
        'table_name' => $table_name,
        'index_name' => $new_index_name
    ));

    if (
        !empty($old_index['sync_stat']['sync_phase']) && !empty($new_index['sync_stat']['sync_phase'])
        &&
        $old_index['sync_stat']['sync_phase'] === $new_index['sync_stat']['sync_phase'] && $new_index['sync_stat']['sync_phase'] == 'INCR'
    ) {
        return true;
    }

    return false;
}

function delete_index($table_name, $old_index)
{
    $otsClient = obtain_ots_client(); 

    $request = [
        'table_name' => $table_name,
        'index_name' => $old_index
    ];
    return $otsClient->deleteSearchIndex( $request );
}


function check_index_exists($table_name, $old_index_name)
{
    $otsClient = obtain_ots_client();
    $index_list = $otsClient->listSearchIndex(array(
        'table_name' => $table_name,
    ));
    
    if ($index_list) {
        foreach ($index_list as $index_info) {
            $index_name = $index_info['index_name'];
            if($index_name == $old_index_name) {
                return true;
            }
        }
    }
    return false;
}