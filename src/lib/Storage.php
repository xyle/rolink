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
namespace rolink\lib;

class Storage
{

    private $driver;

    /**
     * @var object 对象实例
     */
    protected static $instance;

    /**
     * 构造方法，用于构造存储实例
     * @param string $driver 要使用的存储驱动
     * @param array $driverConfig
     * @throws \Exception
     */
    public function __construct($driver = null, $driverConfig = null)
    {
        if (empty($driver)) {
            $driver       = 'Local';
            $driverConfig = [];
        }

        if (empty($driverConfig['driver'])) {
            $storageDriverClass = "\\rolink\\lib\\storage\\$driver";
        } else {
            $storageDriverClass = $driverConfig['driver'];
        }

        $storage = new $storageDriverClass($driverConfig);

        $this->driver = $storage;
    }

    /**
     * 文件上传
     * @param string $file 上传文件路径
     * @param string $filePath 文件路径相对于upload目录
     * @param string $fileType 文件类型,image,video,audio,file
     * @param array $param 额外参数
     * @return mixed
     */
    public function upload($file, $filePath, $fileType = 'image', $param = null)
    {
        return $this->driver->upload($file, $filePath, $fileType, $param);
    }

    /**
     * 初始化
     * @param $type
     * @param $config
     * @return \rolink\lib\Storage
     */
    public static function instance($type = null, $config = null)
    {
        if (is_null(self::$instance)) {
            self::$instance = new static($type, $config);
        }
        return self::$instance;
    }

    /**
     * 获取图片预览地址
     * @param string $file
     * @param string $style
     * @return mixed
     */
    public function getPreviewUrl($file, $style = '')
    {
        return $this->driver->getPreviewUrl($file, $style);
    }

    /**
     * 获取图片地址
     * @param string $file
     * @param string $style
     * @return mixed
     */
    public function getImageUrl($file, $style = '')
    {
        return $this->driver->getImageUrl($file, $style);
    }

    /**
     * 获取文件地址
     * @param string $file
     * @param string $style
     * @return mixed
     */
    public function getUrl($file, $style = '')
    {
        return $this->driver->getUrl($file, $style);
    }

    /**
     * 获取文件下载地址
     * @param string $file
     * @param int $expires
     * @return mixed
     */
    public function getFileDownloadUrl($file, $expires = 3600)
    {
        return $this->driver->getFileDownloadUrl($file, $expires);
    }

    /**
     * 获取云存储域名
     * @return mixed
     */
    public function getDomain()
    {
        return $this->driver->getDomain();
    }

    /**
     * 获取文件相对上传目录路径
     * @param string $url
     * @return mixed
     */
    public function getFilePath($url)
    {
        return $this->driver->getFilePath($url);
    }


}
