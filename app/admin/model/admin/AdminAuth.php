<?php


namespace app\admin\model\admin;


use app\admin\model\BaseModel;

/**
 * 操作权限
 * Class AdminAuth
 * @package app\admin\model\admin
 */
class AdminAuth extends BaseModel
{
    /**
     * 获取权限id 找不到是返回 -1
     * @param string $module
     * @param string $controller
     * @param string $action
     * @return int
     */
    public static function getAuthId(string $module, string $controller,string $action): int
    {
        return self::where("module",$module)->where("controller",$controller)->where("action",$action)->value('id') ?: -1;
    }

    /**
     * 获取菜单
     * @param int $pid
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function getMenu(int $pid = 0): array
    {
        $model = new self;
        $model = $model->where("is_menu",1);
        $model = $model->where("pid",$pid);
        $model = $model->field(['name as title','path as href','icon','id','font_family as fontFamily','is_check as isCheck','spreed']);
        $model = $model->order(["rank desc","id"]);
        $data = $model->select()->each(function ($item)
        {
            $item['children'] = self::getMenu($item['id']);
            $item['isCheck'] = $item['isCheck'] ? 'true' : 'false';
            $item['spreed'] = $item['spreed'] ? 'true' : 'false';
        });
        return $data->toArray() ?: [];
    }
}