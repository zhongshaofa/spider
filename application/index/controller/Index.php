<?php

namespace app\index\controller;


use think\Controller;
use think\Db;

class Index extends Controller {

    /**
     * 网站首页
     */
    public function index() {
        $root = request()->root(true);
        $admin = Db::name('system_config')->where(['name' => 'AdminModuleName', 'group' => 'basic'])->value('value');
        header("location:{$root}/{$admin}.php");
    }
}
