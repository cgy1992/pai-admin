<?php


namespace app\admin\controller\admin;


use app\admin\controller\AuthController;
use app\admin\model\admin\Admin as aModel;
use app\admin\model\admin\AdminRole as rModel;
use app\Request;
use learn\services\UtilService as Util;
use learn\services\JsonService as Json;

/**
 * 账号管理
 * Class Admin
 * @package app\admin\controller\admin
 */
class Admin extends AuthController
{
    /**
     * 账号列表
     * @return string
     * @throws \Exception
     */
    public function index()
    {
        $this->assign("auths",rModel::getAuthLst());
        return $this->fetch();
    }

    /**
     * 账号列表
     * @param Request $request
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function lst(Request $request)
    {
        $where = Util::postMore([
            ['name',''],
            ['role_id',''],
            ['status',''],
            ['page',1],
            ['limit',20],
        ]);
        return Json::successlayui(aModel::systemPage($where));
    }

    /**
     * 添加账号
     * @param Request $request
     */
    public function add(Request $request)
    {

    }
}