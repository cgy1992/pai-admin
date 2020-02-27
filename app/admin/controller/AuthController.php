<?php

namespace app\admin\controller;

use app\admin\model\admin\AdminAuth;
use app\admin\model\admin\AdminRole;
use think\facade\App;
use think\facade\Lang;
use think\facade\Session;

/**
 * 控制器基础类
 */
abstract class AuthController extends SystemBasic
{
    /**
     * model
     * @var
     */
    protected $model = null;

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
     * 当前权限id
     * @var int
     */
    protected $nowAuthId = 0;

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
        $this->controller = unCamelize($this->request->controller());
        $this->action = $this->request->action();
        $this->auth = explode(",", AdminRole::getAuth($this->adminInfo['role_id'] ?: 0));
        $this->nowAuthId = AdminAuth::getAuthId($this->module,$this->controller,$this->action);
        $path = str_split(".",$this->request->controller());
        var_dump("app\{$this->module}\model\{$path[0]}\{$path[1]}");
        $this->model = app("app\{$this->module}\model\{$path[0]}\{$path[1]}");
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
        if (!self::isActive()) exit($this->failedNotice(lang("未登录"),"/admin/login/login"));
        // 权限验证
        if ($this->nowAuthId == -1 || in_array($this->nowAuthId,$this->auth)) return true;
        exit($this->failed(lang('没有权限访问!')));
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
     * 记录日志
     * @return bool
     */
    protected function createLog()
    {
        // 不需要登录不能记录日志
        if (in_array($this->action,$this->noNeedLogin) || $this->noNeedLogin == ['*'] || $this->noNeedLogin == "*") return true;
        // 无需记录日志
        if (in_array($this->action,$this->noNeedLog) || $this->noNeedLog == ['*'] || $this->noNeedLog == "*") return true;
        // 有操作权限，记录日志
        if (in_array($this->nowAuthId,$this->auth)) event("AdminLog",[$this->adminInfo,$this->module,$this->controller,$this->action]);
        return false;
    }
}
