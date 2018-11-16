<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/16
 * Time: 22:08
 */

namespace app\admin\model\spider;

use app\common\service\ModelService;

/**
 * 文章模型数据
 * Class Article
 * @package app\admin_blog\model
 */
class Article extends ModelService {

    /**
     * 绑定数据表
     * @var string
     */
    protected $table = 'spider_article';


    /**
     * 修改文章
     * @param $update
     * @return \think\response\Json
     * @throws \think\exception\PDOException
     */
    public static function edit($update) {
        $update_article = $update;
        unset($update_article['article_id']);
        unset($update_article['tag_list']);
        self::where(['id' => $update['article_id']])->update($update_article);
        \app\admin\model\blog\ArticleTag::where(['article_id' => $update['article_id']])->delete();
        self::__buildAddTag($update['article_id'], $update);
        return __success('文章修改成功！');
    }

    /**
     * 前置数据
     * @param $data
     */
    protected static function __buildAddTag($article_id, $insert) {
        if (isset($insert['tag_list']) && !empty($insert['tag_list']) && !empty($article_id)) {
            list($tag_list, $save_all) = [explode(',', $insert['tag_list']), []];
            foreach ($tag_list as $vo) {
                $tag_id = \app\admin\model\blog\Tag::where(['tag_title' => $vo])->value('id');
                if (empty($tag_id)) {
                    $tag_id = \app\admin\model\blog\Tag::insertGetId(['tag_title' => $vo]);
                }
                $save_all[] = [
                    'article_id' => $article_id,
                    'tag_id'     => $tag_id,
                ];
            }
            \app\admin\model\blog\ArticleTag::insertAll($save_all);
        }
    }

    /**
     * 删除
     * @param $id
     * @return \think\response\Json
     * @throws \think\exception\PDOException
     */
    public static function del($id) {
        is_array($id) ? $del = self::whereIn('id', $id) : $del = self::where('id', $id);
        self::startTrans();
        try {
            $del = $del->update(['is_deleted' => 1]);
            self::commit();
        } catch (\Exception $e) {
            self::rollback();
            return __error($e->getMessage());
        }
        return __success('删除成功');
    }

    /**
     * 获取文章列表数据
     * @param int   $page
     * @param int   $limit
     * @param array $search
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getList($page = 1, $limit = 10, $search = []) {
        $where = [['is_deleted', '=', '0'],];

        //搜索条件
        foreach ($search as $key => $value) {
            if (!empty($value)) {
                switch ($key) {
                    case 'create_time':
                        $value_list = explode(" - ", $value);
                        $where[] = [$key, 'BETWEEN', ["{$value_list[0]} 00:00:00", "{$value_list[1]} 23:59:59"]];
                        break;
                    case 'create_at':
                        $value_list = explode(" - ", $value);
                        $where[] = [$key, 'BETWEEN', ["{$value_list[0]} 00:00:00", "{$value_list[1]} 23:59:59"]];
                        break;
                    default:
                        !empty($value) && $where[] = [$key, 'LIKE', '%' . $value . '%'];
                }
            }
        }

        $count = self::where($where)->count();
        $data = self::where($where)->page($page, $limit)->order(['create_at' => 'desc'])->select();
        empty($data) ? $msg = '暂无数据！' : $msg = '查询成功！';
        $info = [
            'limit'        => $limit,
            'page_current' => $page,
            'page_sum'     => ceil($count / $limit),
        ];
        $list = [
            'code'  => 0,
            'msg'   => $msg,
            'count' => $count,
            'info'  => $info,
            'data'  => $data,
        ];
        return $list;
    }


}