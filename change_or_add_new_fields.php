<?php

require __DIR__ . '/common.php';

$processing = 'create_index';
$processing = 'sync_status';
$processing = 'exchange';
$table_name = 'test_index';
$old_index_name = 'test_index_index';
$new_index_name = $old_index_name . '_new';
$add_fields = [
    [
        'field_name' => 'keyword',
        'field_type' => 'TEXT',
        'index' => true,
        'enable_sort_and_agg' => true,
        'store' => true,
        'is_array' => false
    ]
];

try {
    if ($processing == 'create_index') {    

        create_new_index_by_index_schemas(
            $table_name, $new_index_name, $old_index_name,
            $add_fields, $delete_fields
        );
        
    }
    else if ($processing == 'sync_status') {
        if (false === check_sync_status($table_name, $new_index_name, $old_index_name)) {
            echo '同步中';
        }
        else {
            echo  '同步完成';
        }
    }
    else if ($processing == 'exchange') {
        // 检查是否同步完成
        if (false === check_sync_status($table_name, $new_index_name, $old_index_name)) {
            echo '数据还没有同步完成';
        }
        else {
            echo '数据同步完成';
        }
        // 确定删除旧索引
        if (true === check_index_exists($table_name, $old_index_name)) {
            var_dump(delete_index($table_name, $old_index_name));
        }

        if (true === check_index_exists($table_name, $new_index_name)) {
            var_dump(2111);
            // 用新的索引，旧的索引名字进行重建
            create_new_index_by_index_schemas($table_name, $old_index_name, $new_index_name);
        }
    }

} catch (\Exception $e) {
    echo $e->getMessage();
}



