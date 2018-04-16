<?php
/**
 * Created by PhpStorm.
 * User: panhao
 * Date: 2017/12/18
 * Time: 上午10:53
 * @introduce
 */
namespace app\cms\model;

use app\common\model\BaseModel;

class CommentLike extends BaseModel
{

    protected $table = 'cms_comment_like';

    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    /**
     * @Author panhao
     * @DateTime 2017-12-11
     *
     * @description 新增数据
     * @param $param
     * @return mixed
     */
    public function addCommentLike(array $param)
    {
        return $this->allowField(['user_uuid','comment_uuid'])->save($param);
    }

    /**
     * @Author panhao
     * @DateTime 2017-12-25
     *
     * @description 判断用户是否已点赞
     * @param string $user_uuid
     * @param string $comment_uuid
     * @return int|string
     */
    public function isLiked(string $user_uuid, string $comment_uuid) {
        return $this->where(['user_uuid' => $user_uuid, 'comment_uuid' => $comment_uuid])->count();
    }

    /**
     * @Author panhao
     * @DateTime 2017-12-25
     *
     * @description 取消点赞
     * @param array $where
     * @return int
     */
    public function delCommentLike($where)
    {
        return $this->where($where)->delete();
    }

    /**
     * @Author panhao
     * @DateTime 2017-12-25
     *
     * @description 获取点赞列表
     * @param array $comment_ids
     * @param string $uuid
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getRowsByCid(array $comment_ids, string $uuid) {
        $b = $this->field('user_uuid,comment_uuid')->where(['user_uuid' => $uuid,'comment_uuid' => ['in', $comment_ids]])->select();
        return $b;
    }
}