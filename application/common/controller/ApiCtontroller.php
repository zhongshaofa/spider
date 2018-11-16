<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/17
 * Time: 0:31
 */

namespace app\common\controller;

use think\exception\HttpResponseException;
use think\Controller;
use think\Db;

class ApiCtontroller extends Controller {

    /**
     * 登录用户信息
     * @var string
     */
    protected $member;

    /**
     * 接收的post数据
     * @var
     */
    protected $post;

    /**
     * 接收的get数据
     * @var
     */
    protected $get;

    /**
     * 接收的get获取的post数据
     * @var
     */
    protected $input;

    /**
     * 初始化数据
     * Index constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->get = $this->request->get();
        $this->post = $this->request->post();
        $this->input = $this->request->isPost() ? $this->post : $this->get;
        $this->__checkOauth();
    }

    /**
     * 鉴权
     */
    private function __checkOauth() {
        empty($this->input['access_key']) && $this->__jsonError('access_key不能为空！');
        empty($this->input['secret_key']) && $this->__jsonError('secret_key不能为空！');
        $member = Db::name('spider_member')->where([
            'access_key' => $this->input['access_key'],
            'secret_key' => $this->input['secret_key'],
            'is_deleted' => 0,
        ])->find();
        empty($member) && $this->__jsonError('鉴权失败，秘钥有误！');
        $member['status'] == 0 && $this->__jsonError('用户已被禁用！');
        $member['authorize_time'] == 0 && $this->__jsonError('用户未授权！');
        $authorize_time = strtotime($member['authorize_time']);
        time() > $authorize_time && $this->__jsonError('用户授权已过期！');
        $this->member = $member;
    }

    /**
     * 返回错误信息
     * @param       $msg
     * @param array $data
     * @return \think\response\Json
     */
    protected function __jsonError($msg, $data = []) {
        $dataJson = ['code' => 1, 'msg' => $msg];
        !empty($data) && $dataJson['data'] = $data;
        throw new HttpResponseException(json($dataJson));
    }

    /**
     * 返回正确数据
     * @param       $msg
     * @param array $data
     * @return \think\response\Json
     */
    protected function __jsonSuccess($msg, $data = []) {
        $dataJson = ['code' => 0, 'msg' => $msg];
        !empty($data) && $dataJson['data'] = $data;
        throw new HttpResponseException(json($dataJson));
    }
}