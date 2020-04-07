<?php
// 应用公共文件

/**
 * 通用化API接口数据输出
 * @param int $code
 * @param string $message 信息提示
 * @param array $data
 * @param int $httpCode http状态码
 * @return \think\response\Json
 */
function show($code=1,$message='success',$data=[], $httpCode=200) {

    $data = [
        'code' => intval($code),
        'msg' => $message,
        'data' =>(object)$data,
    ];
    return json($data, $httpCode);
}

if(!function_exists('system_install')) {
    /**
     * @param string $dbHost 数据库地址
     * @param string $dbName 数据库名称
     * @param int $dbPort 端口号
     * @param string $dbUser 数据库账号
     * @param string $dbPassword 数据库密码
     * @param string $account 管理员账号
     * @param string $password 管理员密码
     * @param string $email 管理员邮箱
     * @return bool 是否成功
     * @throws Exception
     */
    function system_install($dbHost,$dbName,$dbPort,$dbUser,$dbPassword, $account, $password, $email)
    {

        $matches = array();
        if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_]{4,19}$/', $account, $matches)) {
            throw new \Exception('Account is required, and should have more than 4 characters and less than 19 characters',40508);
        }
        if (empty($password) || strlen($password) < 6 || strlen($password) > 18) {
            throw new \Exception('Password is required, and should have more than 6 characters and less than 18 characters',1000001);
        }
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \Exception('A valid email is required',40503);
        }

        $sqlContent = @file_get_contents('data/data.sql');

        if (empty($sqlContent)) {
            throw new \Exception('SQL file not exist',1000002);
        }
        $sqlContent = "CREATE DATABASE IF NOT EXISTS {$dbName};".$sqlContent;

        $pdo = new PDO("mysql:host={$dbHost};port={$dbPort}",$dbUser,$dbPassword, [PDO::ATTR_AUTOCOMMIT => 0]);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '?';");
        $stmt->execute([$dbName]);

        if(! ((bool) $stmt->fetchColumn())){
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName`;");
        }

        $pdo->query("use $dbName");

        //$pdo = new PDO('mysql:host=' . $dbHost . ';dbname=' . $dbName . ';port=' . $dbPort, $dbUser, $dbPassword, [PDO::ATTR_AUTOCOMMIT => 0]);

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


        try {

            if ($pdo->beginTransaction()) {
                $pdo->query('set names utf8');//设置编码

                $pdo->exec($sqlContent);

                $password = password_hash($password, PASSWORD_DEFAULT);
                $headimgurl ='/static/images/middle.gif';

                $sql = "INSERT INTO wk_member(account,member_passwd,group_level,nickname,email,create_time,state,headimgurl) SELECT '{$account}','{$password}',0,'{$account}','{$email}',now(),0,'{$headimgurl}' FROM dual WHERE NOT exists(SELECT * FROM wk_member WHERE `account` = '{$account}');";

                $sth = $pdo->prepare($sql, [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);

                if ($sth->execute() === false) {

                    throw new \Exception('sql error', 1000004);
                }

                $pdo->commit();

            } else {
                throw new \Exception('sql error', 1000003);
            }

        } catch (\Exception $ex) {

            $pdo->rollBack();

            throw $ex;
        }
        $pdo->setAttribute(PDO::ATTR_AUTOCOMMIT, 1);



        $params = [
            'APP_DEBUG' => true,
            'HOSTNAME' => $dbHost,
            'HOSTPORT' => $dbPort,
            'DATABASE' => $dbName,
            'USERNAME' => $dbUser,
            'PASSWORD' => $dbPassword
        ];
        if(isset($_SERVER['REQUEST_SCHEME']) && isset($_SERVER['HTTP_HOST'])){
            $url = $_SERVER['REQUEST_SCHEME'] .'://' . $_SERVER['HTTP_HOST'];
            $params['APP_URL'] = $url;
        }
        modify_env($params);

        file_put_contents(root_path().'install.lock', 'true');
        return true;

    }



}
if(!function_exists('modify_env')) {

    /**
     * 修改ENV文件
     * @param array $data
     */
    function modify_env(array $data)
    {
        $envPath = root_path() . DIRECTORY_SEPARATOR . '.env';

        if(!file_exists($envPath)){
            @copy(base_path() . DIRECTORY_SEPARATOR . '.env.example', base_path() . DIRECTORY_SEPARATOR . '.env');
        }
        $contentArray = collect(file($envPath, FILE_IGNORE_NEW_LINES));
        foreach ($contentArray as $key => &$value) {
            foreach ($data as $m => $item) {
                if(strpos($value,$m)!==false && $value!='[DATABASE]'){
                    $contentArray[$key]= preg_replace('/=(.*)/',"= $item",$value);
                }
            }
        }
        $content = implode("\n",$contentArray->toArray());
        file_put_contents($envPath, $content, 0);
    }
}


if(!function_exists('cookie_member')){
    /**
     * 获取或设置登录用户 Cookie
     * @param Member|null $member
     * @param bool $isExpired
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|null|Member|Member[]
     */
    function cookie_member($member, $isExpired = false){
        if($isExpired){
            $cookie = cookie('login_token', '', 60 * 24 * 30*-1);
        }else {
            $data = ['member_id' => $member->member_id, 'unique' => uniqid(), 'last_login_time' => time(), 'user_agent' => request()->header('User-Agent')];

            cookie('login_token', $data, 60 * 24 * 30);
        }
        return $member;
    }
}


if(!function_exists('markdown_converter')) {
    /**
     * 解析 markdown 字符串
     * @param $text
     * @return string
     */
    function markdown_converter($text){

        $environment = League\CommonMark\Environment::createCommonMarkEnvironment();
        $environment->addExtension(new Webuni\CommonMark\TableExtension\TableExtension());
        $environment->addExtension(new Webuni\CommonMark\AttributesExtension\AttributesExtension());

        $environment->addBlockParser(new HttpMethodParser());
        $environment->addInlineParser(new AutoLinkParser());
        $environment->addBlockRenderer('League\CommonMark\Block\Element\Heading',new HeadingRenderer());
        $environment->addBlockRenderer('League\CommonMark\Block\Element\Document',new TocRenderer());
        $environment->addBlockRenderer('SmartWiki\Extentions\Markdown\Element\HttpMethodBlock', new HttpMethodRenderer());

        $converter = new League\CommonMark\Converter(new League\CommonMark\DocParser($environment), new League\CommonMark\HtmlRenderer($environment));


        $html = $converter->convertToHtml($text);

        return $html;
    }
}

if(!function_exists('session_member')) {
    /**
     * 解析 markdown 字符串
     * @param $text
     * @return string
     */
    function session_member($member,$is_remember=null)
    {
        session('member',$member);
        if (strcasecmp($is_remember, 'on') === 0) {
            cookie('login_token',$member,60*60*24*30);
        }else{
            if(cookie('login_token')){
                cookie('login_token',$member,60*60*24*30);
            }
        }
    }
}

if(!function_exists('session_project_role')){
    /**
     * 获取或设置项目访问权限
     * @param int $project_id
     * @param null $value
     * @return mixed
     */
    function session_project_role($project_id,$value = null){
        $key = 'project.role.'. $project_id;
        if(empty($value)){
            return session($key);
        }else{
            session($key,$value);
            return session($key);
        }
    }
}

if(!function_exists('output_word')){
    /**
     * 导出 word
     * @param string $content
     * @return string
     */
    function output_word($content){

        $path = root_path().'public/static/styles/kancloud.css';
        $data = '';

        if(file_exists($path)){
            $data .= file_get_contents($path);
        }
        $content = str_replace("<thead>\n<tr>","<thead><tr style='background-color: rgb(0, 136, 204); color: rgb(255, 255, 255);'>",$content);
        $content = str_replace("<pre><code>","<table width='100%' class='codestyle'><pre><code>",$content);
        $content = str_replace("</code></pre>","</code></pre></table>",$content);


        $html = '<html xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word"  xmlns="http://www.w3.org/TR/REC-html40">
        <head>
        <meta http-equiv=Content-Type content="text/html;  charset=utf-8">
		<style type="text/css">
			table  
			{  
				border-collapse: collapse;
				border: none;  
				width: 100%;  
			}  
			td  
			{  
				border: solid #CCC 1px;  
			}  
			.codestyle{
				word-break: break-all;
				background:silver;mso-highlight:silver;
			}
			'.$data.'
		</style>
        <meta name=ProgId content=Word.Document>
        <meta name=Generator content="Microsoft Word 11">
        <meta name=Originator content="Microsoft Word 11">
        <xml><w:WordDocument><w:View>Print</w:View></xml></head>
        <body>'  .$content.'</body></html>';

        return $html;
    }
}
