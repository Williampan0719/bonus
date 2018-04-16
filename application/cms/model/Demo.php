<?php
/**
 * Created by PhpStorm.
 * User: panhao
 * Date: 2017/12/9
 * Time: 上午10:00
 * @introduce  规范参考demo
 */

namespace app\cms\model;

use traits\model\SoftDelete;
use app\common\model\BaseModel;

class Demo extends BaseModel
{
    use SoftDelete;

    protected $table = 'cms_demo';

    protected $autoWriteTimestamp = 'timestamp';

    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';
    protected $deleteTime = 'deleted_at';

    /**
     * @Author panhao
     * @DateTime 2017-12-09
     *
     * @description 获取全部数据
     * @param $param
     * @return array
     */
    public function getAll(array $param) {
        $a = $this->field('id,title,content,created_at,updated_at')->page($param['page'],$param['size'])->select();
        return $a;
    }

    /**
     * @Author panhao
     * @DateTime 2017-12-09
     *
     * @description 根据id获取列表
     * @param $param
     * @return array
     */
    public function getRowsById($param) {
        $b = $this->field('id,title,content,created_at,updated_at')->order('id asc')->where(['id' => $param['id']])->select();
        return $b;
    }

    /**
     * @Author panhao
     * @DateTime 2017-12-09
     *
     * @description 新增数据
     * @param $param
     * @return mixed
     */
    public function addDemo($param) {
        $demo = new Demo($param);
        $demo->allowField(true)->save();
        return $demo->id;
    }

    /**
     * @Author panhao
     * @DateTime 2017-12-09
     *
     * @description  编辑数据
     * @param array $param
     * @return false|int
     */
    public function editDemo(array $param) {
        return $this->allowField(['title', 'content'])->save($param, ['id' => $param['id']]);
    }

    /**
     * @Author panhao
     * @DateTime 2017-12-09
     *
     * @description
     * @param int $id
     * @return int
     */
    public function delDemo(int $id) {
        return $this->destroy($id);
    }


}