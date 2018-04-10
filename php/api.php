<?php

namespace CalyxEditor;

class Api
{
    /* Generate the key yourself */
    const API_KEY = '1c9e6db7-5570-4e30-816e-4cde272cbab8';

    /* Path for save image */
    const FILES_DIR = 'data/files';

    /* Path for get image by http or https without protocol */
    const PUBLIC_URL = '//localhost/test-calyx/php';

    /* ID THIS STORAGE */
    const ALLOWED_STORAGE_ID = [
        'aa8cc5986507ae578a54c986ce9eb6e4'
    ];
    
    const ALLOWED_EXTENSION = [
        'jpeg', 'jpg', 'png', 'gif', // IMAGE
        'mp4', // VIDEO
        'pdf', 'doc', 'docx' // DOCUMENT
    ];
    
    function __construct()
    {
        header('WWW-Authenticate: Basic realm="Image Realm"');
    }

    public function trySaveFile($indexName)
    {
        if ($this->checkAuthenticate() === false) {
            self::sendResponse(401, ['error' => 'Unauthorized']);
            return false;
        }

        if(self::checkFileSafe($indexName) === false){
            self::sendResponse(400, ['error' => 'You send not allow file']);
            return false;
        }

        $filename = pathinfo($_FILES[$indexName]['name'], PATHINFO_FILENAME);
        $imageName = self::saveFile($indexName, $filename);
        $i = 0;
        while ($imageName === false) {
            $i++;
            $imageName = self::saveFile($indexName, "$filename ($i)");
        }

        self::sendResponse(200, [
            'publicUrl' => $imageName
        ]);
        return $imageName;
    }

    public function getList($type)
    {
        if ($this->checkAuthenticate() === false) {
            self::sendResponse(401, ['error' => 'Unauthorized']);
            return false;
        }

        $userPath = self::FILES_DIR.'/'.$_SERVER['PHP_AUTH_PW'].'/';
        $publicUserPath = self::PUBLIC_URL.'/'.self::FILES_DIR.'/'.$_SERVER['PHP_AUTH_PW'].'/';
        $images = [];
        $dir = new \DirectoryIterator($userPath);

        foreach ($dir as $fileinfo) {
            if ($fileinfo->isDot()) {
                continue;
            }
            if ($fileinfo->isDir()) {
                continue;
            }
            $name = pathinfo($fileinfo->getPathname(),PATHINFO_BASENAME);

            $images[] = [
                'name' => $name,
                'src' => $publicUserPath.$name,
                'timestamp' => $fileinfo->getCTime()
            ];

        }
        self::sendResponse(200, $images);
    }

    private function checkAuthenticate()
    {
        if (!isset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])) {

            return false;
        }
        if ($_SERVER['PHP_AUTH_USER'] !== self::API_KEY || in_array($_SERVER['PHP_AUTH_PW'], self::ALLOWED_STORAGE_ID) !== true) {

            return false;
        }

        return true;
    }

    private static function sendResponse(int $code, array $data)
    {
        http_response_code($code);
        header("Content-type: application/json; charset=utf-8");
        echo json_encode($data);
    }

    private static function checkFileSafe($indexName){
        if (empty($_FILES[$indexName])) {
            return false;
        }
        
        if (!is_uploaded_file($_FILES[$indexName]['tmp_name'])) {
            return false;
        }
        
        $extension = strtolower(pathinfo($_FILES[$indexName]['name'], PATHINFO_EXTENSION));

        if (!in_array($extension, self::ALLOWED_EXTENSION)) {
            return false;
        }
        
        return true;
    }

    private static function saveFile($indexName, $name)
    {

        $extension = pathinfo($_FILES[$indexName]['name'], PATHINFO_EXTENSION);
        $userPath = self::FILES_DIR.'/'.$_SERVER['PHP_AUTH_PW'].'/';
        $path = $userPath.$name.'.'.$extension;

        if (file_exists($path)) {
            return false;
        }

        if (file_exists($userPath) === false) {
            mkdir($userPath, 0777, true);
        }
        
        if (copy($_FILES[$indexName]['tmp_name'], $path)) {
            return self::PUBLIC_URL.'/'.self::FILES_DIR.'/'.$_SERVER['PHP_AUTH_PW'].'/'.$name.'.'.$extension;
        } else {
            return false;
        }
    }
}