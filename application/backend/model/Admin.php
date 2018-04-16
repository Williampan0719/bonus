<?php
/**
 * Created by PhpStorm.
 * User: dongmingcui
 * Date: 2017/12/8
 * Time: 上午10:21
 */

namespace app\backend\model;

use app\common\model\BaseModel;
use traits\model\SoftDelete;

class Admin extends BaseModel
{
    use SoftDelete;

    protected $table = 'backend_admins';

    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';
    protected $deleteTime = 'deleted_at';

    /**
     * @Author liyongchuan
     * @DateTime 2018-01-02
     *
     * @description 后台用户的添加
     * @param array $params
     * @return mixed
     */
    public function adminAdd(array $params)
    {
        $admin = new Admin($params);
        $admin->allowField(true)->save();
        return $admin->admin_id;
    }

    /**
     * @Author liyongchuan
     * @DateTime 2018-01-02
     *
     * @description 获取后台用户详情
     * @param int $adminId
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public function adminDetail(int $adminId)
    {
        return Admin::where('admin_id', $adminId)->field('admin_name,admin_password,admin_mobile,is_super,salt')->find();
    }

    /**
     * @Author liyongchuan
     * @DateTime 2018-01-02
     *
     * @description 后台用户的修改
     * @param array $params
     * @return false|int
     */
    public function adminEdit(array $params)
    {
        return Admin::allowField(['admin_name', 'admin_password', 'is_super','salt'])
            ->save($params, ['admin_id' => $params['admin_id']]);
    }

    /**
     * @Author liyongchuan
     * @DateTime 2018-01-02
     *
     * @description 查询条件
     * @param $query
     * @param $keyword
     */
    protected function scopeAdminWhere($query, $keyword)
    {
        $query->where('admin_name', 'like', '%' . $keyword . '%');
    }

    /**
     * @Author liyongchuan
     * @DateTime 2018-01-02
     *
     * @description 后台用户的列表
     * @param int $page
     * @param int $size
     * @param string $keyword
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function adminList(int $page, int $size, string $keyword)
    {
        return Admin::scope('adminWhere', $keyword)->
        field('admin_id,admin_name,admin_mobile,is_super,created_at,remark,login_ip,login_at')
            ->page($page, $size)->select();
    }

    /**
     * @Author liyongchuan
     * @DateTime 2018-01-02
     *
     * @description 后台用户的总数
     * @param string $keyword
     * @return int|string
     */
    public function adminCount(string $keyword)
    {
        return Admin::scope('adminWhere', $keyword)->count();
    }

    /**
     * @Author liyongchuan
     * @DateTime 2018-01-02
     *
     * @description 后台用户的删除
     * @param int $adminId
     * @return int
     */
    public function adminDelete(int $adminId)
    {
        return Admin::destroy($adminId);
    }
}