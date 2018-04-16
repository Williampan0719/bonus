<?php
/**
 * Created by PhpStorm.
 * User: panhao
 * Date: 2018/1/12
 * Time: 上午11:32
 * @introduce
 */
namespace app\backend\logic;

use app\common\logic\BaseLogic;
use app\system\model\SystemPower;
use think\Exception;

class PowerLogic extends BaseLogic
{
    protected $powerModel;

    public function __construct()
    {
        $this->powerModel = new SystemPower();
    }

    /**
     * @Author panhao
     * @DateTime 2018-01-12
     *
     * @description 后台体力配置
     * @return array
     */
    public function getAll()
    {
        try {
            $data = $this->powerModel->getAll();
            $result = $this->ajaxSuccess(202, ['list' => $data]);

        } catch (Exception $exception) {

            $result = $this->ajaxError(205);
        }

        return $result;
    }

    /**
     * @Author panhao
     * @DateTime 2018-01-12
     *
     * @description 添加配置
     * @param array $param
     * @return array
     */
    public function addPower(array $param)
    {
        try {
            $exist = $this->powerModel->getOne($param['title']);
            if (!empty($exist)) {
                return $this->ajaxError(206,[],'英文名称不能重复');
            }
            $where = ['title'=>$param['title'],'name'=>$param['name'],'num'=>$param['num']];
            $this->powerModel->addPower($where);

            $result = $this->ajaxSuccess(200);
        } catch (Exception $exception) {
            $result = $this->ajaxError(206);
        }
        return $result;
    }

    /**
     * @Author panhao
     * @DateTime 2018-01-19
     *
     * @description 编辑体力值
     * @param array $param
     * @return array
     */
    public function editPower(array $param)
    {
        try {
            $where = ['name'=>$param['name'],'num'=>$param['num']];
            $id = $param['id'];
            $this->powerModel->editPower($where,$id);

            $result = $this->ajaxSuccess(201);
        } catch (Exception $exception) {
            $result = $this->ajaxError(207);
        }
        return $result;
    }
}