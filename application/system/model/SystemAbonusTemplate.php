<?php
/**
 * Created by PhpStorm.
 * User: panhao
 * Date: 2018/1/29
 * Time: 下午3:02
 * @introduce
 */
namespace app\system\model;


use app\common\model\BaseModel;
use traits\model\SoftDelete;

class SystemAbonusTemplate extends BaseModel
{
    use SoftDelete;

    protected $table = 'system_abonus_template';

    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';
    protected $deleteTime = 'deleted_at';

    /**
     * @Author panhao
     * @DateTime 2018-01-29
     *
     * @description 获取模板列表
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getTemplateList($where)
    {
        return $this->field('id,url,word,class,scenes,created_at,updated_at')->where($where)->where('status',1)->order('created_at')->select();
    }

    /**
     * @Author panhao
     * @DateTime 2018-01-29
     *
     * @description 获取模板列表
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getAll()
    {
        return $this->field('id,url,word,class,scenes,created_at,updated_at,status')->order('created_at')->select();
    }

    /**
     * @Author panhao
     * @DateTime 2018-01-29
     *
     * @description 添加模板
     * @param array $param
     * @return false|int
     */
    public function addTemplate(array $param)
    {
        return $this->allowField(true)->save($param);
    }

    /**
     * @Author panhao
     * @DateTime 2018-01-29
     *
     * @description 编辑模板
     * @param array $param
     * @return false|int
     */
    public function editTemplate(array $param)
    {
        return $this->allowField(['url','word','updated_at','status'])->save($param,['id'=>$param['id']]);
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-11
     *
     * @description 删除模版
     * @param int $id
     * @return int
     */
    public function delTemplate(int $id)
    {
        return $this->destroy(['id'=>$id]);
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-02
     *
     * @description 获取单条
     * @param array $param
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public function getOne(array $param)
    {
        return $this->where($param)->field('id,class,scenes,url,word,created_at,updated_at')->find();
    }

}