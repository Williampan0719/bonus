<?php
/**
 * Created by PhpStorm.
 * User: panhao
 * Date: 2017/11/20
 * Time: 下午4:48
 */

namespace app\cms\model;

use traits\model\SoftDelete;
use app\common\model\BaseModel;

class AdvClass extends BaseModel
{
    use SoftDelete;

    protected $table = 'cms_adv_class';

    protected $autoWriteTimestamp = 'timestamp';

    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';
    protected $deleteTime = 'deleted_at';

    /**
     * @Author panhao
     * @DateTime 2017-12-04
     *
     * @description 获取全部数据
     * @param $param
     * @return array
     */
    public function getAll(array $param) {
        return $this->field('id,class_nid,created_at,updated_at,class_name,end_type,class_title')->page($param['page'],$param['size'])->select();
    }

    /**
     * @Author panhao
     * @DateTime 2017-12-18
     *
     * @description 根据id获取列表
     * @param $param
     * @return array
     */
    public function getRowsById($param) {
        return $this->field('id,class_nid,created_at,updated_at,class_name,end_type,class_title')->order('id asc')->where(['id' => $param['id']])->find();
    }

    /**
     * @Author panhao
     * @DateTime 2017-12-18
     *
     * @description 新增数据
     * @param $param
     * @return mixed
     */
    public function addAdvClass($param) {
        $adv = new AdvClass($param);
        $adv->allowField(['class_nid','class_name','end_type','class_title'])->save();
        return $adv->id;
    }

    /**
     * @Author panhao
     * @DateTime 2017-12-18
     *
     * @description  编辑数据
     * @param array $param
     * @return false|int
     */
    public function editAdvClass(array $param) {

        return AdvClass::allowField(['class_nid','class_name','end_type','class_title'])->save($param, ['id' => $param['id']]);
    }

    /**
     * @Author panhao
     * @DateTime 2017-12-18
     *
     * @description
     * @param int $id
     * @return int
     */
    public function delAdvClass(int $id) {
        return AdvClass::destroy($id);
    }

}