<?php
/**
 * Created by PhpStorm.
 * User: dongmingcui
 * Date: 2017/12/8
 * Time: 上午10:26
 */

namespace app\backend\model;

use app\common\model\BaseModel;

class RoleAdmin extends BaseModel
{
    protected $table = 'backend_role_admin';
    protected $autoWriteTimestamp = false;

    /**
     * @Author liyongchuan
     * @DateTime 2018-01-02
     *
     * @description 后台用户与角色的关联添加
     * @param string $role_id
     * @param int $adminId
     * @return array|false
     */
    public function roleAdminAdd(string $role_id, int $adminId)
    {
        $roleId = explode(',', $role_id);
        $data = [];
        foreach ($roleId as $key => $vo) {
            $data[$key]['admin_id'] = $adminId;
            $data[$key]['role_id'] = $vo;
        }
        return RoleAdmin::saveAll($data);
    }

    /**
     * @Author liyongchuan
     * @DateTime 2018-01-02
     *
     * @description 查询adminID对应的角色
     * @param int $adminId
     * @return array
     */
    public function roleAdminDetail(int $adminId)
    {
        return RoleAdmin::where('admin_id',$adminId)->column('role_id');
    }

    /**
     * @Author liyongchuan
     * @DateTime 2018-01-02
     *
     * @description 根据adminId删除数据
     * @param int $adminId
     * @return int
     */
    public function roleAdminDelete(int $adminId)
    {
        return RoleAdmin::where('admin_id',$adminId)->delete();
    }
}