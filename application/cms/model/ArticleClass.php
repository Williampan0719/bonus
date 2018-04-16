<?php
/**
 * Created by PhpStorm.
 * User: panhao
 * Date: 2017/11/15
 * Time: 下午4:48
 */

namespace app\cms\model;

use traits\model\SoftDelete;
use app\common\model\BaseModel;

class ArticleClass extends BaseModel
{
    use SoftDelete;

    protected $table = 'cms_article_class';

    protected $autoWriteTimestamp = 'timestamp';

    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';
    protected $deleteTime = 'deleted_at';

    /**
     * @Author panhao
     * @DateTime 2017-12-18
     *
     * @description 获取父级全部数据
     * @param $param
     * @return array
     */
    public function getFirstAll(array $param) {
        $a = $this->field('id,class_name,created_at,updated_at,pid,level')->page($param['page'],$param['size'])->where(['level' => 1])->select();
        return $a;
    }

    /**
     * @Author panhao
     * @DateTime 2017-12-04
     *
     * @description 获取子级全部数据
     * @param $param
     * @return array
     */
    public function getChildAll(int $id) {
        $a = $this->field('id,class_name,created_at,updated_at,pid,level')->where(['pid' => $id, 'level' => 2])->select();
        return $a;
    }

    /**
     * @Author panhao
     * @DateTime 2017-12-05
     *
     * @description 根据id获取列表
     * @param $param
     * @return array
     */
    public function getRowsById($param) {
        $b = $this->field('id,class_name,created_at,updated_at')->order('id asc')->where(['id' => $param['id']])->find();
        return $b;
    }

    /**
     * @Author panhao
     * @DateTime 2017-12-05
     *
     * @description 新增数据
     * @param $param
     * @return mixed
     */
    public function addArticleClass($param) {
        $article = new ArticleClass($param);
        $article->allowField(true)->save();
        return $article->id;
    }

    /**
     * @Author panhao
     * @DateTime 2017-12-05
     *
     * @description  编辑数据
     * @param array $param
     * @return false|int
     */
    public function editArticleClass(array $param) {

        return ArticleClass::allowField(['class_name'])->save($param, ['id' => $param['id']]);
    }

    /**
     * @Author panhao
     * @DateTime 2017-12-05
     *
     * @description 删除数据
     * @param int $id
     * @return int
     */
    public function delArticleClass(int $id) {
        ArticleClass::destroy($id);
        return ArticleClass::destroy(['pid' => $id]);
    }


}