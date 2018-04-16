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

class Role extends BaseModel
{
    use SoftDelete;
    protected $table = 'backend_roles';

    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';
    protected $deleteTime = 'deleted_at';

    /**
     * @Author liyongchuan
     * @DateTime 2018-01-02
     *
     * @description 角色的新增
     * @param array $params
     * @return mixed
     */
    public function roleAdd(array $params)
    {
        $role = new Role($params);
        $role->allowField(true)->save();
        return $role->id;
    }

    /**
     * @Author liyongchuan
     * @DateTime 2018-01-02
     *
     * @description 角色的详情
     * @param int $roleId
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public function roleDetail(int $roleId)
    {
        return Role::where('id', $roleId)->field('name,display_name,description')->find();
    }
    /**
     * @Author liyongchuan
     * @DateTime 2018-01-02
     *
     * @description 角色的修改
     * @param array $params
     * @return false|int
     */
    public function roleEdit(array $params)
    {
        return Role::allowField(['name', 'display_name', 'description'])
            ->save($params, ['id' => $params['id']]);
    }
    /**
     * @Author liyongchuan
     * @DateTime 2018-01-02
     *
     * @description 角色的删除
     * @param int $adminId
     * @return int
     */
    public function roleDelete(int $adminId)
    {
        return Role::destroy($adminId);
    }

    /**
     * @Author liyongchuan
     * @DateTime 2018-01-02
     *
     * @description 查询条件
     * @param $query
     * @param $keyword
     */
    protected function scopeRoleWhere($query, $keyword)
    {
        $query->where('display_name', 'like', '%' . $keyword . '%');
    }

    /**
     * @Author liyongchuan
     * @DateTime 2018-01-02
     *
     * @description 角色的列表
     * @param int $page
     * @param int $size
     * @param string $keyword
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function roleList(int $page, int $size, string $keyword)
    {
        return Role::scope('roleWhere', $keyword)->page($page, $size)->select();
    }

    /**
     * @Author liyongchuan
     * @DateTime 2018-01-02
     *
     * @description 角色的总数
     * @param string $keyword
     * @return int|string
     */
    public function roleCount(string $keyword)
    {
        return Role::scope('roleWhere', $keyword)->count();
    }

    /**
     * @Author liyongchuan
     * @DateTime 2018-01-02
     *
     * @description 角色全部
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function roleAll()
    {
        return Role::field('id,display_name')->select();
    }
}