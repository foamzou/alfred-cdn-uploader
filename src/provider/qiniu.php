<?php
require_once 'src/vendor/autoload.php';

use Qiniu\Storage\UploadManager;
use Qiniu\Auth;
use Ramsey\Uuid\Uuid;

class QiniuProvider {
    private static $upManager;
    private static $token;
    private static $domain;


    public static function upload($filename, $ext, $filePath)
    {
        if (empty(self::$upManager)) {
            $accessKey = getenv('qiniu_ak');
            $secretKey = getenv('qiniu_sk');
            $bucketName = getenv('qiniu_bucket_name');
            self::$domain = getenv('qiniu_domain');
    
            self::$upManager = new UploadManager();
            $auth = new Auth($accessKey, $secretKey);
            self::$token = $auth->uploadToken($bucketName);
        }
    
        $uploadFilename = (Uuid::uuid4())->toString() . '.' . $ext;
        list($ret, $err) = self::$upManager->putFile(self::$token, $uploadFilename, $filePath);
        
        if($err !== null) {
            return [$filename, false];
        } else {
            return [$filename, self::$domain . '/' . $ret['key']];
        }
    }
}