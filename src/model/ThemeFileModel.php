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
namespace rolink\model;

use think\Model;

class ThemeFileModel extends Model
{
    /**
     * 模型名称
     * @var string
     */
    protected $name = 'theme_file';

    protected $type = [
        'more'        => 'array',
        'config_more' => 'array',
        'draft_more'  => 'array'
    ];

}
