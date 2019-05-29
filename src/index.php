<?php

require_once 'src/provider/qiniu.php';

class Client
{
    public function run($fileList)
    {
        $fileList = explode("\t", $fileList);

        if (empty($fileList) || empty($fileList[0])) {
            $tmpFile = '/tmp/alfred-uploader-' . time() . '.png';
            shell_exec('bin/pngpaste ' . $tmpFile);
            $fileList = [$tmpFile];
        }
        $response = [];
        foreach ($fileList as $file) {
            $filename = explode('/', $file);
            $filename = $filename[count($filename) - 1];

            $ext = explode('.', $file);
            $ext = $ext[count($ext) - 1];
            list($filename, $url) = QiniuProvider::upload($filename, $ext, $file);
            $response[] = [
                'name' => $filename,
                'url' => $url,
            ];
        }
        if (!empty($tmpFile)) {
            @unlink($tmpFile);
        }

        if (count($response) == 1) {
            echo $response[0]['url'];
        } else {
            foreach ($response as $fileItem) {
                echo "{$fileItem['name']}: {$fileItem['url']}\n";
            }
        }
    }
}

$query = "{query}";

(new Client())->run($query);
