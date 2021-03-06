<?php


namespace app\model;


use think\Model;

class Logs extends Model
{
    /**
     * @param $content
     * @param $user_id
     * @param null $original_data
     * @param null $present_data
     * @return bool
     */
    public static function addLogs($content,$user_id,$original_data = null,$present_data = null)
    {
        $logs = new Logs();
        $logs->create_at = $user_id;
        $logs->content = $content;
        $logs->original_data = $original_data;
        $logs->present_data = $present_data;
        $logs->create_time = date('Y-m-d H:i:s');

        return $logs->save();
    }
}