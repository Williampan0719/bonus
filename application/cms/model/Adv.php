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

class Adv extends BaseModel
{
    use SoftDelete;

    protected $table = 'cms_adv';

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
        $a = $this->field('id,title,pic,content,sequence,is_show,rmd,url,class_id,created_at,updated_at')->page($param['page'],$param['size'])->select();
        return $a;
    }

    /**
     * @Author panhao
     * @DateTime
     *
     * @description 根据id获取列表
     * @param $param
     * @return array
     */
    public function getRowsByClassId($param) {
        $b = $this->field('id,title,pic,content,sequence,is_show,rmd,url,class_id,created_at,updated_at')->order('id asc')->where(['class_id' => $param['class_id']])->page($param['page'], $param['size'])->select();
        return $b;
    }

    /**
     * @Author panhao
     * @DateTime
     *
     * @description 新增数据
     * @param $param
     * @return mixed
     */
    public function addAdv($param) {
        $adv = new Adv($param);
        $adv->allowField(true)->save();
        return $adv->id;
    }

    /**
     * @Author panhao
     * @DateTime
     *
     * @description  编辑数据
     * @param array $param
     * @return false|int
     */
    public function editAdv(array $param) {
        return $this->allowField(['title', 'pic'])->save($param, ['id' => $param['id']]);
    }

    /**
     * @Author panhao
     * @DateTime
     *
     * @description
     * @param int $id
     * @return int
     */
    public function delAdv(int $id) {
        return $this->destroy($id);
    }

}