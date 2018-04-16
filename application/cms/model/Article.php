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

class Article extends BaseModel
{
    use SoftDelete;

    protected $table = 'cms_article';

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
        $a = $this->field('id,class_id,created_at,updated_at,title,abstract,content,hits,img,keywords,is_show,rmd,status,aid,author,sequence')->page($param['page'],$param['size'])->select();
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
        $b = $this->field('id,class_id,created_at,updated_at,title,abstract,content,hits,img,keywords,is_show,rmd,status,aid,author,sequence')->order('id asc')->where(['id' => $param['id']])->find();
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
    public function addArticle($param) {
        $article = new Article($param);
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
    public function editArticle(array $param) {

        return Article::allowField(['title', 'abstract', 'content', 'class_id','img','keywords','is_show','rmd','status','aid','author','sequence'])->save($param, ['id' => $param['id']]);
    }

    /**
     * @Author panhao
     * @DateTime 2017-12-05
     *
     * @description 删除数据
     * @param int $id
     * @return int
     */
    public function delArticle(int $id) {
        return Article::destroy($id);
    }

}