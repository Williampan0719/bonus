<?php
/**
 * Created by PhpStorm.
 * User: panhao
 * Date: 2017/12/28
 * Time: 下午3:03
 * @introduce 动态消息列表
 */
namespace app\cms\model;

use traits\model\SoftDelete;
use app\common\model\BaseModel;

class CirclesMessage extends BaseModel
{
    use SoftDelete;

    protected $table = 'cms_circles_message';

    protected $autoWriteTimestamp = 'timestamp';

    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';
    protected $deleteTime = 'deleted_at';

    /**
     * @Author panhao
     * @DateTime 2017-12-28
     *
     * @description 新增消息
     * @param array $param
     * @return false|int
     */
    public function addMessage(array $param) {
        return $this->allowField(true)->save($param);
    }

    /**
     * @Author panhao
     * @DateTime 2017-12-28
     *
     * @description 删除消息
     * @param array $where
     * @return int
     */
    public function delMessage(array $where) {
        return $this->destroy($where);
    }

    /**
     * @Author panhao
     * @DateTime 2017-12-28
     *
     * @description 消息列表
     * @param array $where
     * @return int
     */
    public function getRowsByUser(array $where) {
        return $this->field('id,from_user_uuid,to_user_uuid,content,is_read,link_uuid,type,created_at,updated_at')->where($where)->order('is_read desc, created_at desc')->select();
    }

    /**
     * @Author panhao
     * @DateTime 2017-12-28
     *
     * @description 标为已读
     * @param array $ids
     * @return false|int
     */
    public function readMessage(array $ids) {
        return $this->allowField('is_read')->save(['is_read'=>1],['id'=>['in',$ids]]);
    }

    /**
     * @Author panhao
     * @DateTime 2017-12-28
     *
     * @description 获取未读条数
     * @param array $param
     * @return int|string
     */
    public function getUnreadCount(array $param) {
        return $this->where($param)->count();
    }

    /**
     * @Author panhao
     * @DateTime 2017-12-28
     *
     * @description 获取最新一条数据
     * @param string $uuid
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public function getNewOne(string $uuid){
        return $this->field('id,from_user_uuid,to_user_uuid,content,is_read,link_uuid,type,created_at,updated_at')->where(['to_user_uuid'=>$uuid,'is_read'=>0])->order('created_at desc')->find();
    }
}