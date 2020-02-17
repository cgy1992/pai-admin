<?php


namespace app\admin\controller\pinduoduo;


use app\admin\controller\AuthController;
use app\admin\model\pinduoduo\PinduoduoProvider as pModel;
use app\admin\model\pinduoduo\PinduoduoStore as sModel;
use app\Request;
use learn\utils\Curl;
use think\facade\Cache;
use think\facade\Session;
use learn\services\JsonService as Json;
use learn\services\UtilService as Util;

/**
 * 商品信息
 * Class Goods
 * @package app\admin\controller\pinduoduo
 */
class Goods extends AuthController
{
    /**
     * 商品列表
     * @return string|void
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index()
    {
        $store = sModel::isBingInfo($this->adminId);
        if (!$store) $this->assign("isBind",false);
        else
        {
            if (!authIsExit($this->adminId))
            {
                $provider = pModel::get($store['pid']);
                if (!$provider)
                {
                    $this->assign("isBind",true);
                    $this->assign("provider",$provider);
                }
                else
                {
                    Session::set("provider",$provider);
                    return $this->redirect("https://mms.pinduoduo.com/open.html?response_type=code&client_id={$provider['client_id']}&redirect_uri=http://learn.leapy.cn/admin/pinduoduo.authorization/accessauth&state=2000");
                }
            }else $this->assign("isBind",true);
        }
        return $this->fetch();
    }

    /**
     * 商品列表
     * @param Request $request
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function lst(Request $request)
    {
        $where = Util::postMore([
            ['goods_name',''],
            ['is_onsale',''],
            ['page',1],
            ['page_size',20],
        ]);
        $where['type'] = 'pdd.goods.list.get';
        $provider = pModel::getClientByAid($this->adminId);
        $token = Cache::store('redis')->get('store_'.$this->adminId);
        $data['client_id'] = $provider['client_id'];
        $data['access_token'] = $token['access_token'];
        $data['timestamp'] = time();
        $data['data_type'] = 'JSON';
        $curl = new Curl("https://gw-api.pinduoduo.com/api/router","POST",$where);
        $curl->header(["Content-Type:application/json"]);
        $curl->buildSign($provider['client_secret']);
        $res = json_decode($curl->run(),true);
        var_dump($res);
    }
}