<?php
declare (strict_types = 1);

namespace app\middleware;

use think\facade\Cache;

class Authenticate
{
    /**
     * 处理请求
     *
     * @param \think\Request $request
     * @param \Closure       $next
     * @return Response
     */
    public function handle($request, \Closure $next)
    {
        $server=$request->server();
        $uri = $server['REQUEST_URI'];
        $member=session('member');
        $config=Cache::get('config');
        if(empty($member) && !$config['ENABLE_ANONYMOUS']){

            if(stripos($uri,'/account')===false){
                $url=url('account/login');
                return redirect((string)$url);
            }
        }
        if(empty($member)){
            if(stripos($uri,'/member')===false){
                $url=url('account/login');
             //   return redirect((string)$url);
            }
        }
        return $next($request);
    }
}
