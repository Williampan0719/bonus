<?php

namespace app\cron\controller;

use app\common\controller\BaseController;

class Index extends BaseController
{
    public function index()
    {
        return 'this is cron method';
    }
}
