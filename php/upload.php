<?php

namespace CalyxEditor;

require_once('./api.php');

if (isset($_FILES['file'])) {
    $api = new \CalyxEditor\Api();
    $api->trySaveFile('file');
} 
