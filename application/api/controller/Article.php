<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/17
 * Time: 0:31
 */

namespace app\api\controller;


use app\common\controller\ApiCtontroller;
use think\Db;

class Article extends ApiCtontroller {

    /**
     * 获取文章列表API
     */
    public function index() {
        $per_page = empty($this->post['per_page']) ? 10 : $this->post['per_page'];
        $article_list = Db::name('spider_article')->where([
            'status'     => 1,
            'is_deleted' => 0,
        ])->order('create_at', 'desc')->paginate($per_page);
        empty($article_list) && $this->__jsonError('暂无文章数据');
        $this->__jsonSuccess('获取文章成功！', $article_list);
    }
}