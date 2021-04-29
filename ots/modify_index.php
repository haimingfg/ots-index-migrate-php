<?php

use Aliyun\OTS\Consts\FieldTypeConst;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../common.php';



$interactor = new Ahc\Cli\IO\Interactor;

// 输入表名
$table_name = $interactor->prompt("迁移的表", null, null, 5);
$old_index_name = $interactor->prompt("迁移索引名", null, null, 5); 
$new_index_name = $interactor->prompt("新索引名", null, null, 5);  

$add_fields = [
    [
        'field_name' => 'sort',
        'field_type' => FieldTypeConst::LONG,
        'index' => true,
        'enable_sort_and_agg' => true,
        'store' => true,
        'is_array' => false
    ]
];
// $delete_fields = [
//     'sort' => '_',
// ];

do {
    $is_processing = true;
    // 创建索引
    if (false === check_index_exists($table_name, $new_index_name)) {
        echo '创建索引：', $new_index_name, "\n";
    //  用新的索引，旧的索引名字进行重建
        create_new_index_by_index_schemas(
            $table_name, $new_index_name, $old_index_name, $add_fields, $delete_fields);
    }
    else {
        echo '已经存在索引：', $new_index_name, "\n";
    }

    // 是否迁移完成
    if (false == check_sync_status($table_name, $new_index_name, $old_index_name)) {
    // if (true) {    
        echo $old_index_name, '=>', $new_index_name, '，数据同步';
        sleep(1);
        echo 'wait....', "\n";
    }

    // 更改索引名
    // 删除旧的
    $is_exchange = $interactor->confirm('是否已经在代码更换好索引' . $new_index_name . '?', 'n');  
    if ($is_exchange) {
        echo '删除索引，', $old_index_name, "\n";
        delete_index($table_name, $old_index_name);
        echo '创建索引，', $old_index_name, "\n";
        if (false === check_index_exists($table_name, $old_index_name)) {
            // 用新的索引，旧的索引名字进行重建
            create_new_index_by_index_schemas($table_name, $old_index_name, $new_index_name);
        }

       // 是否迁移完成
        if (false == check_sync_status($table_name, $new_index_name, $old_index_name)) {
        // if (true) {    
            echo $new_index_name, '=>', $old_index_name, '，数据同步';
            sleep(1);
            echo 'wait....', "\n";
        }
        
        echo '迁移完成', "\n";
        $is_exchange = $interactor->confirm('是否已经在代码更换好索引' . $old_index_name . '?', 'n'); 
        if ($is_exchange) {
            $is_exchange = $interactor->confirm('是否需要删除' . $new_index_name . '?', 'y'); 
            if ($is_exchange) {
                echo '删除索引，', $new_index_name;
                delete_index($table_name, $new_index_name); 
            }
            $is_processing = false;
        } 
        else {
            echo '退出....';
            break;
        }
    }
    else {
        echo '退出....';
        break;
    }
} while ($is_processing);