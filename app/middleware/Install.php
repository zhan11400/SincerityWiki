<?php
declare (strict_types = 1);

namespace app\middleware;

class Install
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
        /*$path = root_path().'install.lock';
        $server=$request->server();
        $uri = $server['REQUEST_URI'];

        if(stripos($uri,'/install') === 0){
            if( file_exists($path)) {
                return redirect('/');
            }

        }elseif(file_exists($path) === false){
            $url ='/install.php';

            return redirect($url);
        }*/

        return $next($request);
    }
}
