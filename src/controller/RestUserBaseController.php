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
namespace rolink\controller;

class RestUserBaseController extends RestBaseController
{

    public function initialize()
    {

        if (empty($this->user)) {
            $this->error(['code' => 10001, 'msg' => '登录已失效!']);
        }

    }

}
