<?php
/**
 * Created by PhpStorm.
 * User: zhan
 * Date: 19/09/26
 * Time: 上午2:57
 */
namespace app\lib\exception;
use think\Exception;

class ApiException extends Exception {

    public $message = '';
    public $httpCode = 500;
    public $code = 0;
    /**
     * @param string $message
     * @param int $httpCode
     * @param int $code
     */
    public function __construct($message = '', $code = 0, $httpCode = 0) {
     $this->httpCode = $httpCode;
        $this->message = $message;
       $this->code = $code;
    }
}