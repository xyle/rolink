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
return [
    'app_dispatch'=>[
        "type"        => 1,//钩子类型(默认为应用钩子;1:核心钩子;2:应用钩子;3:模板钩子;4:后台模板钩子)
        "name"        => '应用调度', // 钩子名称
        "description" => "应用调度", //钩子描述
        "once"        => 0 // 是否只执行一次
    ],
    'before_content'     => [
        "type"        => 3,//钩子类型(默认为应用钩子;2:应用钩子;3:模板钩子;4:后台模板钩子)
        "name"        => '主要内容之前', // 钩子名称
        "description" => "主要内容之前", //钩子描述
        "once"        => 0 // 是否只执行一次
    ],
    'log_write_done'     => [
        "type"        => 1,//钩子类型(默认为应用钩子;1:核心钩子;2:应用钩子;3:模板钩子;4:后台模板钩子)
        "name"        => '日志写入完成', // 钩子名称
        "description" => "日志写入完成", //钩子描述
        "once"        => 0 // 是否只执行一次
    ],
    'switch_theme'       => [
        "type"        => 1,//钩子类型(默认为应用钩子;1:核心钩子;2:应用钩子;3:模板钩子;4:后台模板钩子)
        "name"        => '前台模板切换', // 钩子名称
        "description" => "前台模板切换", //钩子描述
        "once"        => 1 // 是否只执行一次
    ],
    'switch_admin_theme' => [
        "type"        => 1,//钩子类型(默认为应用钩子;1:核心钩子;2:应用钩子;3:模板钩子;4:后台模板钩子)
        "name"        => '后台模板切换', // 钩子名称
        "description" => "后台模板切换", //钩子描述
        "once"        => 1 // 是否只执行一次
    ],
    'captcha_image'      => [
        "type"        => 1,//钩子类型(默认为应用钩子;1:核心钩子;2:应用钩子;3:模板钩子;4:后台模板钩子)
        "name"        => '验证码图片', // 钩子名称
        "description" => "验证码图片", //钩子描述
        "once"        => 1 // 是否只执行一次
    ],
];
