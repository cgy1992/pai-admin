<?php

namespace app\admin\controller;

use learn\basic\admin\BaseController;
use think\facade\App;
use think\facade\Lang;
use think\facade\Session;

/**
 * 控制器基础类
 */
abstract class AuthController extends BaseController
{
    /**
     * 当前登陆管理员信息
     * @var
     */
    protected $adminInfo;

    /**
     * 当前登陆管理员ID
     * @var int
     */
    protected $adminId;

    /**
     * 当前管理员权限
     * @var array
     */
    protected $auth = [];

    /**
     * 无需登录的方法,同时也就不需要鉴权了
     * @var array
     */
    protected $noNeedLogin = [];

    /**
     * 无需鉴权的方法,但需要登录
     * @var array
     */
    protected $noNeedRight = [];

    /**
     * 无需记录日志
     * @var array
     */
    protected $noNeedLog = [];

    /**
     * 当前模块
     * @var string
     */
    private $module = "";

    /**
     * 当前控制器
     * @var string
     */
    private $controller = "";

    /**
     * 当前方法名
     * @var string
     */
    private $action = "";

    /**
     * 初始化
     */
    protected function initialize()
    {
        parent::initialize(); // TODO: Change the autogenerated stub
        $this->adminInfo = Session::get("adminInfo");
        $this->adminId = Session::get("adminId");
        $this->module = App::getInstance()->http->getName();
        $this->controller = $this->request->controller();
        $this->action = $this->request->action();
        var_dump($this->module);
        var_dump($this->controller);
        var_dump($this->action);
        // 鉴权
        $this->checkAuth();
        // 多语言
        $this->loadLang();
        // 日志
        $this->createLog();
    }

    /**
     * 检验权限
     */
    protected function checkAuth()
    {
        // 不需要登录
        if (in_array($this->action,$this->noNeedLogin) || $this->noNeedLogin == ['*'] || $this->noNeedLogin == "*") return true;
        // 验证登录
        if (!self::isActive()) return $this->redirect($this->request->domain().'/admin/login/login');
    }

    /**
     * 加载语言文件
     */
    protected function loadLang()
    {
        Lang::load(App::getRootPath() . 'app/' . $this->module . '/lang/' . Lang::getLangSet() . '/' . $this->controller . '.php');
    }

    /**
     * 验证登录
     * @return bool
     */
    protected static function isActive()
    {
        return Session::has('adminId') && Session::has('adminInfo');
    }

    /**
     * 创建日志
     * @return bool
     */
    protected function createLog()
    {
        // 不需要登录不能记录日志
        if (in_array($this->action,$this->noNeedLogin) || $this->noNeedLogin == ['*'] || $this->noNeedLogin == "*") return true;
        // 无需记录日志
        if (in_array($this->action,$this->noNeedLog) || $this->noNeedLog == ['*'] || $this->noNeedLog == "*") return true;
        // 日志记录
        event("AdminLog",[$this->adminInfo,$this->module,$this->controller,$this->action]);
    }
}
