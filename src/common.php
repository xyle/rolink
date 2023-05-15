<?php
// +----------------------------------------------------------------------
// | Rolink
// +----------------------------------------------------------------------
// | Copyright (c) 2018-present http://www.rolink-power.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Author: Xyle <2262078363@qq.com>
// +----------------------------------------------------------------------
use think\facade\Db;
use rolink\model\OptionModel;
use dir\Dir;
use think\facade\Route;
use rolink\lib\Storage;
use think\facade\Cache;

// 应用公共文件

//php8.0
if (!defined('T_NAME_RELATIVE')) {
    define('T_NAME_RELATIVE', T_NS_SEPARATOR);
}

/**
 * Url生成
 * @param string      $url    路由地址
 * @param array       $vars   变量
 * @param bool|string $suffix 生成的URL后缀
 * @param bool|string $domain 域名
 * @return UrlBuild
 */
function url(string $url = '', array $vars = [], $suffix = true, $domain = false)
{
    return Route::buildUrl($url, $vars)->suffix($suffix)->domain($domain)->build();
}

/**
 * 调用模块的操作方法 参数格式 [模块/控制器/]操作
 * @param string       $url          调用地址
 * @param string|array $vars         调用参数 支持字符串和数组
 * @param string       $layer        要调用的控制层名称
 * @param bool         $appendSuffix 是否添加类名后缀
 * @return mixed
 */
function action($url, $vars = [], $layer = 'controller', $appendSuffix = false)
{
    $app           = app();
    $rootNamespace = $app->getRootNamespace();
    $urlArr        = explode('/', $url);
    $appName       = $urlArr[0];
    $controller    = parse_name($urlArr[1], 1, true);
    $action        = $urlArr[2];

    return $app->invokeMethod(["{$rootNamespace}\\$appName\\$layer\\$controller" . ucfirst($layer), $action], $vars);
}

if (!function_exists('db')) {
    /**
     * 实例化数据库类
     * @param string $name   操作的数据表名称（不含前缀）
     * @param string $config 数据库配置参数
     * @param bool   $force  是否强制重新连接
     * @return \think\db\Query
     */
    function db($name = '', $config = null, $force = false)
    {
        return Db::connect($config, $force)->name($name);
    }
}

/**
 * 获取当前登录的管理员ID
 * @return int
 */
function get_current_admin_id()
{
    return session('ADMIN_ID');
}

/**
 * 返回带协议的域名
 */
function get_domain()
{
    return request()->domain();
}

/**
 * 获取网站根目录
 * @return string 网站根目录
 */
function get_root()
{
    $root = "";
//    $root = str_replace("//", '/', $root);
//    $root = str_replace('/index.php', '', $root);
//    if (defined('APP_NAMESPACE') && APP_NAMESPACE == 'api') {
//        $root = preg_replace('/\/api(.php)$/', '', $root);
//    }
//
//    $root = rtrim($root, '/');

    return $root;
}

/**
 * 获取当前后台主题名
 * @return string
 */
function get_current_admin_theme()
{
    if (PHP_SAPI != 'cli') {

        static $_currentAdminTheme;

        if (!empty($_currentAdminTheme)) {
            return $_currentAdminTheme;
        }
    }

    $t     = '_at';
    $theme = config('template.rolink_admin_default_theme');

    $rolinkDetectTheme = true;
    if ($rolinkDetectTheme) {
        if (isset($_GET[$t])) {
            $theme = $_GET[$t];
            cookie('rolink_admin_theme', $theme, 864000);
        } elseif (cookie('rolink_admin_theme')) {
            $theme = cookie('rolink_admin_theme');
        }
    }

    $_currentAdminTheme = $theme;

    return $theme;
}

/**
 * 获取用户头像地址
 * @param $avatar 用户头像文件路径,相对于 upload 目录
 * @return string
 */
function get_user_avatar_url($avatar)
{
    if (!empty($avatar)) {
        if (strpos($avatar, "http") === 0) {
            return $avatar;
        } else {
            return get_image_url($avatar, 'avatar');
        }

    } else {
        return $avatar;
    }

}

/**
 * 密码加密方法
 * @param string $pw       要加密的原始密码
 * @param string $authCode 加密字符串
 * @return string
 */
function password($pw, $authCode = '')
{
    if (empty($authCode)) {
        $authCode = config('database.authcode');
    }
    $result = "###" . md5(md5($authCode . $pw));
    return $result;
}

/**
 * 密码比较方法,所有涉及密码比较的地方都用这个方法
 * @param string $password     要比较的密码
 * @param string $passwordInDb 数据库保存的已经加密过的密码
 * @return boolean 密码相同，返回true
 */
function compare_password($password, $passwordInDb)
{
    return password($password) == $passwordInDb;
}

/**
 * 随机字符串生成
 * @param int $len 生成的字符串长度
 * @return string
 */
function random_string($len = 6)
{
    $chars    = [
        "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k",
        "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v",
        "w", "x", "y", "z", "A", "B", "C", "D", "E", "F", "G",
        "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R",
        "S", "T", "U", "V", "W", "X", "Y", "Z", "0", "1", "2",
        "3", "4", "5", "6", "7", "8", "9"
    ];
    $charsLen = count($chars) - 1;
    shuffle($chars);    // 将数组打乱
    $output = "";
    for ($i = 0; $i < $len; $i++) {
        $output .= $chars[mt_rand(0, $charsLen)];
    }
    return $output;
}

/**
 * 清空系统缓存
 */
function clear_cache()
{
    // 清除 opcache缓存
    if (function_exists("opcache_reset")) {
        opcache_reset();
    }

    $runtimePath = runtime_path();
    $dirs        = [];
    $rootDirs    = scan_dir($runtimePath . "*");
    $noNeedClear = ['.', '..', 'log', 'session'];
    $rootDirs    = array_diff($rootDirs, $noNeedClear);
    foreach ($rootDirs as $dir) {

        if ($dir != "." && $dir != "..") {
            $dir = $runtimePath . $dir;
            if (is_dir($dir)) {
                array_push($dirs, $dir);
            } 
        }
    }
    $dirTool = new Dir($runtimePath);
    foreach ($dirs as $dir) {
        $dirTool->delDir($dir);
    }
    
    Cache::clear();
}

/**
 * 获取html文本里的img
 * @param string $content html 内容
 * @return array 图片列表 数组item格式<pre>
 *                        [
 *                        "src"=>'图片链接',
 *                        "title"=>'图片标签的 title 属性',
 *                        "alt"=>'图片标签的 alt 属性'
 *                        ]
 *                        </pre>
 */
function get_content_images($content)
{
    \phpQuery::newDocumentHTML($content);
    $pq         = pq(null);
    $images     = $pq->find("img");
    $imagesData = [];
    if ($images->length) {
        foreach ($images as $img) {
            $img            = pq($img);
            $image          = [];
            $image['src']   = $img->attr("src");
            $image['title'] = $img->attr("title");
            $image['alt']   = $img->attr("alt");
            array_push($imagesData, $image);
        }
    }
    \phpQuery::$documents = null;
    return $imagesData;
}

/**
 * 去除字符串中的指定字符
 * @param string $str   待处理字符串
 * @param string $chars 需去掉的特殊字符
 * @return string
 */
function strip_chars($str, $chars = '?<*.>\'\"')
{
    return preg_replace('/[' . $chars . ']/is', '', $str);
}

/**
 * 转化数据库保存的文件路径，为可以访问的url
 * @param string $file
 * @param mixed  $style 图片样式,支持各大云存储
 * @return string
 */
function get_asset_url($file, $style = '')
{
    if (strpos($file, "http") === 0) {
        return $file;
    } else if (strpos($file, "/") === 0) {
        return $file;
    } else {
        $storage = Storage::instance();
        return $storage->getUrl($file, $style);
    }
}

/**
 * 转化数据库保存图片的文件路径，为可以访问的url
 * @param string $file  文件路径，数据存储的文件相对路径
 * @param string $style 图片样式,支持各大云存储
 * @return string 图片链接
 */
function get_image_url($file, $style = 'watermark')
{
    if (empty($file)) {
        return '';
    }

    if (strpos($file, "http") === 0) {
        return $file;
    } else if (strpos($file, "/") === 0) {
        return get_domain() . $file;
    } else {
        $storage = Storage::instance();
        return $storage->getImageUrl($file, $style);
    }
}

/**
 * 获取图片预览链接
 * @param string $file  文件路径，相对于upload
 * @param string $style 图片样式,支持各大云存储
 * @return string
 */
function get_image_preview_url($file, $style = 'watermark')
{
    if (empty($file)) {
        return '';
    }

    if (strpos($file, "http") === 0) {
        return $file;
    } else if (strpos($file, "/") === 0) {
        return $file;
    } else {
        $storage = Storage::instance();
        return $storage->getPreviewUrl($file, $style);
    }
}

/**
 * 获取文件下载链接
 * @param string $file    文件路径，数据库里保存的相对路径
 * @param int    $expires 过期时间，单位 s
 * @return string 文件链接
 */
function get_file_download_url($file, $expires = 3600)
{
    if (empty($file)) {
        return '';
    }

    if (strpos($file, "http") === 0) {
        return $file;
    } else if (strpos($file, "/") === 0) {
        return $file;
    } else if (strpos($file, "#") === 0) {
        return $file;
    } else {
        $storage = Storage::instance();
        return $storage->getFileDownloadUrl($file, $expires);
    }
}

/**
 * 解密用str_encode加密的字符串
 * @param        $string    要解密的字符串
 * @param string $key       加密时salt
 * @param int    $expiry    多少秒后过期
 * @param string $operation 操作,默认为DECODE
 * @return bool|string
 */
function str_decode($string, $key = '', $expiry = 0, $operation = 'DECODE')
{
    $ckey_length = 4;

    $key  = md5($key ? $key : config("database.authcode"));
    $keya = md5(substr($key, 0, 16));
    $keyb = md5(substr($key, 16, 16));
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';

    $cryptkey   = $keya . md5($keya . $keyc);
    $key_length = strlen($cryptkey);

    $string        = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
    $string_length = strlen($string);

    $result = '';
    $box    = range(0, 255);

    $rndkey = [];
    for ($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }

    for ($j = $i = 0; $i < 256; $i++) {
        $j       = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp     = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }

    for ($a = $j = $i = 0; $i < $string_length; $i++) {
        $a       = ($a + 1) % 256;
        $j       = ($j + $box[$a]) % 256;
        $tmp     = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        $result  .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }

    if ($operation == 'DECODE') {
        if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
            return substr($result, 26);
        } else {
            return '';
        }
    } else {
        return $keyc . str_replace('=', '', base64_encode($result));
    }

}

/**
 * 加密字符串
 * @param        $string 要加密的字符串
 * @param string $key    salt
 * @param int    $expiry 多少秒后过期
 * @return bool|string
 */
function str_encode($string, $key = '', $expiry = 0)
{
    return str_decode($string, $key, $expiry, "ENCODE");
}

/**
 * 获取文件相对路径
 * @param string $assetUrl 文件的URL
 * @return string
 */
function asset_relative_url($assetUrl)
{
    if (strpos($assetUrl, "http") === 0) {
        return $assetUrl;
    } else {
        return str_replace('/upload/', '', $assetUrl);
    }
}

/**
 * 获取应用类名，
 * @param $name      纯字母应用名，如:admin
 * @return string
 */
function get_app_class($name)
{
    $name        = strtolower($name);
    $classPrefix = ucwords($name);
    $class       = "app\\{$name}\\{$classPrefix}App";
    return $class;
}

/**
 * 替代scan_dir的方法
 * @param string $pattern 检索模式 搜索模式 *.txt,*.doc; (同glog方法)
 * @param int    $flags
 * @param        $pattern
 * @return array
 */
function scan_dir($pattern, $flags = 0)
{
    $files = glob($pattern, $flags);
    if (empty($files)) {
        $files = [];
    } else {
        $files = array_map('basename', $files);
    }

    return $files;
}

/**
 * 获取某个目录下所有子目录
 * @param $dir
 * @return array
 */
function sub_dirs($dir)
{
    $dir     = ltrim($dir, "/");
    $dirs    = [];
    $subDirs = scan_dir("$dir/*", GLOB_ONLYDIR);
    if (!empty($subDirs)) {
        foreach ($subDirs as $subDir) {
            $subDir = "$dir/$subDir";
            array_push($dirs, $subDir);
            $subDirSubDirs = sub_dirs($subDir);
            if (!empty($subDirSubDirs)) {
                $dirs = array_merge($dirs, $subDirSubDirs);
            }
        }
    }

    return $dirs;
}

/**
 * 验证码检查，验证完后销毁验证码
 * @param string $value 要验证的字符串
 * @param string $id    验证码的ID
 * @param bool   $reset 验证成功后是否重置
 * @return bool
 */
function captcha_check($value, $id = "", $reset = true)
{
    return \think\captcha\facade\Captcha::check($value);
}

/**
 * 切分SQL文件成多个可以单独执行的sql语句
 * @param        $file            string sql文件路径
 * @param        $tablePre        string 表前缀
 * @param string $charset         字符集
 * @param string $defaultTablePre 默认表前缀
 * @param string $defaultCharset  默认字符集
 * @return array
 */
function split_sql($file, $tablePre, $charset = 'utf8mb4', $defaultTablePre = 'rolink_', $defaultCharset = 'utf8mb4')
{
    if (file_exists($file)) {
        //读取SQL文件
        $sql = file_get_contents($file);
        $sql = str_replace("\r", "\n", $sql);
        $sql = str_replace("BEGIN;\n", '', $sql);//兼容 navicat 导出的 insert 语句
        $sql = str_replace("COMMIT;\n", '', $sql);//兼容 navicat 导出的 insert 语句
        $sql = str_replace($defaultCharset, $charset, $sql);
        $sql = trim($sql);
        //替换表前缀
        $sql  = str_replace(" `{$defaultTablePre}", " `{$tablePre}", $sql);
        $sqls = explode(";\n", $sql);
        return $sqls;
    }

    return [];
}

/**
 * 判断当前的语言包，并返回语言包名
 * @return string  语言包名
 */
function current_lang()
{
    return app()->lang->getLangSet();
}

/**
 * 获取惟一订单号
 * @return string
 */
function get_order_sn()
{
    return date('Ymd') . substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
}

/**
 * 获取文件扩展名
 * @param string $filename 文件名
 * @return string 文件扩展名
 */
function get_file_extension($filename)
{
    $pathinfo = pathinfo($filename);
    return strtolower($pathinfo['extension']);
}

/**
 * 检查手机或邮箱是否还可以发送验证码,并返回生成的验证码
 * @param string  $account 手机或邮箱
 * @param integer $length  验证码位数,支持4,6,8
 * @return string 数字验证码
 * @throws \think\db\exception\DataNotFoundException
 * @throws \think\db\exception\ModelNotFoundException
 * @throws \think\exception\DbException
 */
function get_verification_code($account, $length = 6)
{
    if (empty($account)) return false;
    $verificationCodeQuery = Db::name('verification_code');
    $currentTime           = time();
    $maxCount              = 5;
    $findVerificationCode  = $verificationCodeQuery->where('account', $account)->find();
    $result                = false;
    if (empty($findVerificationCode)) {
        $result = true;
    } else {
        $sendTime       = $findVerificationCode['send_time'];
        $todayStartTime = strtotime(date('Y-m-d', $currentTime));
        if ($sendTime < $todayStartTime) {
            $result = true;
        } else if ($findVerificationCode['count'] < $maxCount) {
            $result = true;
        }
    }

    if ($result) {
        switch ($length) {
            case 4:
                $result = rand(1000, 9999);
                break;
            case 6:
                $result = rand(100000, 999999);
                break;
            case 8:
                $result = rand(10000000, 99999999);
                break;
            default:
                $result = rand(100000, 999999);
        }
    }

    return $result;
}

/**
 * 更新手机或邮箱验证码发送日志
 * @param string $account    手机或邮箱
 * @param string $code       验证码
 * @param int    $expireTime 过期时间
 * @return int|string
 * @throws \think\Exception
 * @throws \think\db\exception\DataNotFoundException
 * @throws \think\db\exception\ModelNotFoundException
 * @throws \think\exception\DbException
 * @throws \think\exception\PDOException
 */
function verification_code_log($account, $code, $expireTime = 0)
{
    $currentTime = time();
    $expireTime  = $expireTime > $currentTime ? $expireTime : $currentTime + 30 * 60;

    $findVerificationCode = Db::name('verification_code')->where('account', $account)->find();

    if ($findVerificationCode) {
        $todayStartTime = strtotime(date("Y-m-d"));//当天0点
        if ($findVerificationCode['send_time'] <= $todayStartTime) {
            $count = 1;
        } else {
            $count = Db::raw('count+1');
        }
        $result = Db::name('verification_code')
            ->where('account', $account)
            ->update([
                'send_time'   => $currentTime,
                'expire_time' => $expireTime,
                'code'        => $code,
                'count'       => $count
            ]);
    } else {
        $result = Db::name('verification_code')
            ->insert([
                'account'     => $account,
                'send_time'   => $currentTime,
                'code'        => $code,
                'count'       => 1,
                'expire_time' => $expireTime
            ]);
    }

    return $result;
}

/**
 * 手机或邮箱验证码检查，验证完后销毁验证码增加安全性,返回true验证码正确，false验证码错误
 * @param string  $account 手机或邮箱
 * @param string  $code    验证码
 * @param boolean $clear   是否验证后销毁验证码
 * @return string  错误消息,空字符串代码验证码正确
 * @return string
 * @throws \think\Exception
 * @throws \think\db\exception\DataNotFoundException
 * @throws \think\db\exception\ModelNotFoundException
 * @throws \think\exception\DbException
 * @throws \think\exception\PDOException
 */
function check_verification_code($account, $code, $clear = false)
{

    $findVerificationCode = Db::name('verification_code')->where('account', $account)->find();
    if ($findVerificationCode) {
        if ($findVerificationCode['expire_time'] > time()) {

            if ($code == $findVerificationCode['code']) {
                if ($clear) {
                    Db::name('verification_code')->where('account', $account)->update(['code' => '']);
                }
            } else {
                return "验证码不正确!";
            }
        } else {
            return "验证码已经过期,请先获取验证码!";
        }

    } else {
        return "请先获取验证码!";
    }

    return "";
}

/**
 * 清除某个手机或邮箱的数字验证码,一般在验证码验证正确完成后
 * @param string $account 手机或邮箱
 * @return boolean true：手机验证码正确，false：手机验证码错误
 * @throws \think\Exception
 * @throws \think\exception\PDOException
 */
function clear_verification_code($account)
{
    $verificationCodeQuery = Db::name('verification_code');
    $result                = $verificationCodeQuery->where('account', $account)->update(['code' => '']);
    return $result;
}

/**
 * 区分大小写的文件存在判断
 * @param string $filename 文件地址
 * @return boolean
 */
function file_exists_case($filename)
{
    if (is_file($filename)) {
        if (APP_DEBUG) {
            if (basename(realpath($filename)) != basename($filename))
                return false;
        }
        return true;
    }
    return false;
}

/**
 * 生成用户 token
 * @param $userId
 * @param $deviceType
 * @return string 用户 token
 * @throws \think\Exception
 * @throws \think\db\exception\DataNotFoundException
 * @throws \think\db\exception\ModelNotFoundException
 * @throws \think\exception\DbException
 * @throws \think\exception\PDOException
 */
function generate_user_token($userId, $deviceType)
{
    $userTokenQuery = Db::name("user_token")
        ->where('user_id', $userId)
        ->where('device_type', $deviceType);
    $findUserToken  = $userTokenQuery->find();
    $currentTime    = time();
    $expireTime     = $currentTime + 24 * 3600 * 7;
    $token          = md5(uniqid()) . md5(uniqid());
    if (empty($findUserToken)) {
        Db::name("user_token")->insert([
            'token'       => $token,
            'user_id'     => $userId,
            'expire_time' => $expireTime,
            'create_time' => $currentTime,
            'device_type' => $deviceType
        ]);
    } else {
        if ($findUserToken['expire_time'] > time() && !empty($findUserToken['token'])) {
            $token = $findUserToken['token'];
        } else {
            Db::name("user_token")
                ->where('user_id', $userId)
                ->where('device_type', $deviceType)
                ->update([
                    'token'       => $token,
                    'expire_time' => $expireTime,
                    'create_time' => $currentTime
                ]);
        }

    }

    return $token;
}

/**
 * 字符串命名风格转换
 * type 0 将Java风格转换为C的风格 1 将C风格转换为Java的风格
 * @param string  $name    字符串
 * @param integer $type    转换类型
 * @param bool    $ucfirst 首字母是否大写（驼峰规则）
 * @return string
 */
function parse_name($name, $type = 0, $ucfirst = true)
{
    if ($type) {
        $name = preg_replace_callback('/_([a-zA-Z])/', function ($match) {
            return strtoupper($match[1]);
        }, $name);
        return $ucfirst ? ucfirst($name) : lcfirst($name);
    }

    return strtolower(trim(preg_replace("/[A-Z]/", "_\\0", $name), "_"));
}

/**
 * 判断字符串是否为已经序列化过
 * @param $str
 * @return bool
 */
function is_serialized($str)
{
    return ($str == serialize(false) || @unserialize($str) !== false);
}

/**
 * 判断是否SSL协议
 * @return boolean
 */
function is_ssl()
{
    if (isset($_SERVER['HTTPS']) && ('1' == $_SERVER['HTTPS'] || 'on' == strtolower($_SERVER['HTTPS']))) {
        return true;
    } elseif (isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT'])) {
        return true;
    }
    return false;
}

/**
 * 获取客户端IP地址
 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
 * @param boolean $adv  是否进行高级模式获取（有可能被伪装）
 * @return string
 */
function get_client_ip($type = 0, $adv = true)
{
    return request()->ip($type, $adv);
}

/**
 * 生成base64的url,用于数据库存放 url
 * @param $url    路由地址，如 控制器/方法名，应用/控制器/方法名
 * @param $params url参数
 * @return string
 */
function url_encode($url, $params)
{
    // 解析参数
    if (is_string($params)) {
        // aaa=1&bbb=2 转换成数组
        parse_str($params, $params);
    }

    return base64_encode(json_encode(['action' => $url, 'param' => $params]));
}

/**
 * Url生成
 * @param string       $url    路由地址
 * @param string|array $vars   变量
 * @param bool|string  $suffix 生成的URL后缀
 * @param bool|string  $domain 域名
 * @return string
 * @throws \think\db\exception\DataNotFoundException
 * @throws \think\db\exception\ModelNotFoundException
 * @throws \think\exception\DbException
 */
function u($url = '', $vars = '', $suffix = true, $domain = false)
{
    global $GV_routes;

    if (empty($GV_routes)) {
        $routeModel    = new \app\admin\model\RouteModel();
        $GV_routes = $routeModel->getRoutes();
    }

    if (false === strpos($url, '://') && 0 !== strpos($url, '/')) {
        $info = parse_url($url);
        $url  = !empty($info['path']) ? $info['path'] : '';
        if (isset($info['fragment'])) {
            // 解析锚点
            $anchor = $info['fragment'];
            if (false !== strpos($anchor, '?')) {
                // 解析参数
                list($anchor, $info['query']) = explode('?', $anchor, 2);
            }
            if (false !== strpos($anchor, '@')) {
                // 解析域名
                list($anchor, $domain) = explode('@', $anchor, 2);
            }
        } elseif (strpos($url, '@') && false === strpos($url, '\\')) {
            // 解析域名
            list($url, $domain) = explode('@', $url, 2);
        }
    }

    // 解析参数
    if (is_string($vars)) {
        // aaa=1&bbb=2 转换成数组
        parse_str($vars, $vars);
    }

    if (isset($info['query'])) {
        // 解析地址里面参数 合并到vars
        parse_str($info['query'], $params);
        $vars = array_merge($params, $vars);
    }

    if (!empty($vars) && !empty($GV_routes[$url])) {

        foreach ($GV_routes[$url] as $actionRoute) {
            $sameVars = array_intersect_assoc($vars, $actionRoute['vars']);

            if (count($sameVars) == count($actionRoute['vars'])) {
                ksort($sameVars);
                $url  = $url . '?' . http_build_query($sameVars);
                $vars = array_diff_assoc($vars, $sameVars);
                break;
            }
        }
    }

    if (!empty($anchor)) {
        $url = $url . '#' . $anchor;
    }

    return url($url, $vars, $suffix, $domain);
}

/**
 * 判断 核心是否安装
 * @return bool
 */
function is_installed()
{
    static $IsInstalled;
    if (empty($IsInstalled)) {
        $IsInstalled = file_exists(ROLINK_DATA . 'install.lock');
    }
    return $IsInstalled;
}

/**
 * 替换编辑器内容中的文件地址
 * @param string  $content     编辑器内容
 * @param boolean $isForDbSave true:表示把绝对地址换成相对地址,用于数据库保存,false:表示把相对地址换成绝对地址用于界面显示
 * @return string
 */
function replace_content_file_url($content, $isForDbSave = false)
{
    \phpQuery::newDocumentHTML($content);
    $pq = pq(null);

    $storage       = Storage::instance();
    $localStorage  = new rolink\lib\storage\Local([]);
    $storageDomain = $storage->getDomain();
    $domain        = request()->host();

    $images = $pq->find("img");
    if ($images->length) {
        foreach ($images as $img) {
            $img    = pq($img);
            $imgSrc = $img->attr("src");

            if ($isForDbSave) {
                if (preg_match("/^\/upload\//", $imgSrc)) {
                    $img->attr("src", preg_replace("/^\/upload\//", '', $imgSrc));
                } elseif (preg_match("/^http(s)?:\/\/$domain\/upload\//", $imgSrc)) {
                    $img->attr("src", $localStorage->getFilePath($imgSrc));
                } elseif (preg_match("/^http(s)?:\/\/$storageDomain\//", $imgSrc)) {
                    $img->attr("src", $storage->getFilePath($imgSrc));
                }

            } else {
                $img->attr("src", get_image_url($imgSrc));
            }

        }
    }

    $links = $pq->find("a");
    if ($links->length) {
        foreach ($links as $link) {
            $link = pq($link);
            $href = $link->attr("href");

            if ($isForDbSave) {
                if (preg_match("/^\/upload\//", $href)) {
                    $link->attr("href", preg_replace("/^\/upload\//", '', $href));
                } elseif (preg_match("/^http(s)?:\/\/$domain\/upload\//", $href)) {
                    $link->attr("href", $localStorage->getFilePath($href));
                } elseif (preg_match("/^http(s)?:\/\/$storageDomain\//", $href)) {
                    $link->attr("href", $storage->getFilePath($href));
                }

            } else {
                if (!(preg_match("/^\//", $href) || preg_match("/^http/", $href))) {
                    $link->attr("href", get_file_download_url($href));
                }

            }

        }
    }

    $content = $pq->htmlOuter();

    \phpQuery::$documents = null;


    return $content;

}

/**
 * 获取后台风格名称
 * @return string
 */
function get_admin_style()
{
    $adminSettings = get_option('admin_settings');
    return empty($adminSettings['admin_style']) ? 'admin_rolink' : $adminSettings['admin_style'];
}

/**
 * curl get 请求
 * @param $url
 * @return mixed
 */
function curl_get($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FAILONERROR, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_AUTOREFERER, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $SSL = substr($url, 0, 8) == "https://" ? true : false;
    if ($SSL) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 信任任何证书
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); // 检查证书中是否设置域名
    }
    $content = curl_exec($ch);
    curl_close($ch);
    return $content;
}

/**
 * 文件大小格式化
 * @param $bytes 文件大小（字节 Byte)
 * @return string
 */
function file_size_format($bytes)
{
    $type = ['B', 'KB', 'MB', 'GB', 'TB'];
    for ($i = 0; $bytes >= 1024; $i++)//单位每增大1024，则单位数组向后移动一位表示相应的单位
    {
        $bytes /= 1024;
    }
    return (floor($bytes * 100) / 100) . $type[$i];//floor是取整函数，为了防止出现一串的小数，这里取了两位小数
}

/**
 * 获取ThinkPHP版本
 * @return string
 */
function thinkphp_version()
{
    return \think\facade\App::version();
}

/**
 * 获取版本
 * @return string
 */
function rolink_version()
{
    try {
        $version = trim(file_get_contents(ROLINK_ROOT . 'version'));
    } catch (\Exception $e) {
        $version = '1.0.0';
    }
    return $version;
}

/**
 * 获取核心包目录
 */
function core_path()
{
    return __DIR__ . DIRECTORY_SEPARATOR;
}

function mobile_mask($mobile)
{
    return substr($mobile, 0, 3) . '****' . substr($mobile, -4, 4);
}
