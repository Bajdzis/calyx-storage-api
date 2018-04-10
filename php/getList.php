<?php

namespace CalyxEditor;

require_once('./api.php');

$allowType = ['IMAGE', 'VIDEO', 'DOCUMENT'];


if (isset($_POST['type'])) {
    $api = new \CalyxEditor\Api();
    if(in_array($_POST['type'], $allowType)){
        $api->getList($_POST['type']);
    }
} 
