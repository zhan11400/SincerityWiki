<?php


namespace app\model;


use think\facade\Cache;
use think\Model;

class Config extends Model
{
    /**
     * @param $key
     * @param null $default
     * @return mixed|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function getFirstOrDefault($key,$default = null)
    {
        $config = Config::where('key','=',$key)->find();

        return $config ? $config->value : $default;
    }

    /**
     * @param bool $update
     * @return array|bool|mixed
     */
    public static function getConfigFromCache($update = false)
    {
        $cacheKey = 'config';
        $config = Cache::get($cacheKey);
        if(empty($config) || $update==true) {
            $config = Config::where('id','>',0)->column("value",'key');
            if(empty($config)){
                return false;
            }
            //更新后重新写入缓存
            Cache::set($cacheKey,$config,36000);
        }
        return $config;
    }
}