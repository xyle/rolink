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

class OptionModel extends Model
{
    /**
     * 模型名称
     * @var string
     */
    protected $name = 'option';

    /**
     * 数据表字段类型
     * @var array
     */
    protected $type = [
        'option_value' => 'array',
    ];


}
