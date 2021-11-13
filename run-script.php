<?php

require_once 'src/provider/qiniu.php';

class Client
{
    public function run($fileList)
    {
        $shouldCompressForPng = getenv('compress_png') ? true : false;
        $fileList = explode("\t", $fileList);
        $tmpFileList = [];
        // fileList 为空，说明是文件来自剪贴板
        if (empty($fileList) || empty($fileList[0])) {
            $tmpFile = '/tmp/alfred-uploader-' . time() . '.png';
            $tmpFileList[] = $tmpFile;
            shell_exec('bin/pngpaste ' . $tmpFile);
            if ($shouldCompressForPng) {
                $compressedFile = $tmpFile . "-compressed.png";
                shell_exec("bin/pngquant -o {$compressedFile} {$tmpFile}");
                $tmpFileList[] = $compressedFile;
                $tmpFile = $compressedFile;
            }
            
            $fileList = [$tmpFile];
        }
        $response = [];
        foreach ($fileList as $file) {
            $filename = explode('/', $file);
            $filename = $filename[count($filename) - 1];

            $ext = explode('.', $file);
            $ext = $ext[count($ext) - 1];
            if ($shouldCompressForPng && $ext == "png") {
                $compressedFile = $file . "-compressed.png";
                shell_exec("bin/pngquant -o {$compressedFile} {$file}");
                $file = $compressedFile;
                $tmpFileList[] = $compressedFile;
            }
            list($filename, $url) = QiniuProvider::upload($filename, $ext, $file);
            $response[] = [
                'name' => $filename,
                'url' => $url,
            ];
        }

        // clear tmp file
        foreach ($tmpFileList as $tmpFile) {
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
