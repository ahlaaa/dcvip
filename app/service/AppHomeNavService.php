<?php
// +----------------------------------------------------------------------
// | ShopXO 国内领先企业级B2C免费开源电商系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011~2099 http://shopxo.net All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( https://opensource.org/licenses/mit-license.php )
// +----------------------------------------------------------------------
// | Author: Devil
// +----------------------------------------------------------------------
namespace app\service;

use think\facade\Db;
use app\service\SystemService;
use app\service\ResourcesService;

/**
 * APP首页导航服务层
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class AppHomeNavService
{
    /**
     * 列表数据处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-08-01
     * @desc    description
     * @param   [array]          $data   [数据列表]
     * @param   [array]          $params [输入参数]
     */
    public static function AppHomeNavListHandle($data, $params = [])
    {
        if(!empty($data))
        {
            $common_platform_type = MyConst('common_platform_type');
            $common_app_event_type = MyConst('common_app_event_type');
            foreach($data as &$v)
            {
                // 平台类型
                if(isset($v['platform']))
                {
                    $v['platform_text'] = $common_platform_type[$v['platform']]['name'];
                }

                // 事件类型
                if(isset($v['event_type']) && $v['event_type'] != -1)
                {
                    $v['event_type_text'] = $common_app_event_type[$v['event_type']]['name'];
                }

                // 图片地址
                if(isset($v['images_url']))
                {
                    $v['images_url'] = ResourcesService::AttachmentPathViewHandle($v['images_url']);
                }

                // 时间
                if(isset($v['add_time']))
                {
                    $v['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
                }
                if(isset($v['upd_time']))
                {
                    $v['upd_time'] = empty($v['upd_time']) ? '' : date('Y-m-d H:i:s', $v['upd_time']);
                }
            }
        }
        return $data;
    }

    /**
     * 首页导航数据保存
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-12-19
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function AppHomeNavSave($params = [])
    {
        // 请求类型
        $p = [
            [
                'checked_type'      => 'length',
                'key_name'          => 'name',
                'checked_data'      => '2,60',
                'error_msg'         => '名称长度 2~60 个字符',
            ],
            [
                'checked_type'      => 'in',
                'key_name'          => 'platform',
                'checked_data'      => array_column(MyConst('common_platform_type'), 'value'),
                'error_msg'         => '平台类型有误',
            ],
            [
                'checked_type'      => 'in',
                'key_name'          => 'event_type',
                'checked_data'      => array_column(MyConst('common_app_event_type'), 'value'),
                'is_checked'        => 2,
                'error_msg'         => '事件值类型有误',
            ],
            [
                'checked_type'      => 'length',
                'key_name'          => 'event_value',
                'checked_data'      => '255',
                'error_msg'         => '事件值最多 255 个字符',
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'images_url',
                'checked_data'      => '255',
                'error_msg'         => '请上传图片',
            ],
            [
                'checked_type'      => 'length',
                'key_name'          => 'sort',
                'checked_data'      => '3',
                'error_msg'         => '顺序 0~255 之间的数值',
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        // 附件
        $data_fields = ['images_url'];
        $attachment = ResourcesService::AttachmentParams($params, $data_fields);

        // 数据
        $data = [
            'name'          => $params['name'],
            'platform'      => $params['platform'],
            'event_type'    => isset($params['event_type']) ? intval($params['event_type']) : -1,
            'event_value'   => $params['event_value'],
            'images_url'    => $attachment['data']['images_url'],
            'bg_color'      => isset($params['bg_color']) ? $params['bg_color'] : '',
            'sort'          => intval($params['sort']),
            'is_enable'     => isset($params['is_enable']) ? intval($params['is_enable']) : 0,
            'is_need_login' => isset($params['is_need_login']) ? intval($params['is_need_login']) : 0,
        ];

        if(empty($params['id']))
        {
            $data['add_time'] = time();
            if(Db::name('AppHomeNav')->insertGetId($data) > 0)
            {
                return DataReturn(MyLang('common.insert_success'), 0);
            }
            return DataReturn(MyLang('common.insert_fail'), -100);
        } else {
            $data['upd_time'] = time();
            if(Db::name('AppHomeNav')->where(['id'=>intval($params['id'])])->update($data))
            {
                return DataReturn(MyLang('common.edit_success'), 0);
            }
            return DataReturn(MyLang('common.edit_fail'), -100);
        }
    }

    /**
     * 首页导航删除
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-12-18
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function AppHomeNavDelete($params = [])
    {
        // 参数是否有误
        if(empty($params['ids']))
        {
            return DataReturn('操作id有误', -1);
        }
        // 是否数组
        if(!is_array($params['ids']))
        {
            $params['ids'] = explode(',', $params['ids']);
        }

        // 删除操作
        if(Db::name('AppHomeNav')->where(['id'=>$params['ids']])->delete())
        {
            return DataReturn(MyLang('common.delete_success'), 0);
        }

        return DataReturn(MyLang('common.delete_fail'), -100);
    }

    /**
     * 首页导航状态更新
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-06T21:31:53+0800
     * @param    [array]          $params [输入参数]
     */
    public static function AppHomeNavStatusUpdate($params = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'id',
                'error_msg'         => '操作id有误',
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'field',
                'error_msg'         => '操作字段有误',
            ],
            [
                'checked_type'      => 'in',
                'key_name'          => 'state',
                'checked_data'      => [0,1],
                'error_msg'         => '状态有误',
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        // 数据更新
        if(Db::name('AppHomeNav')->where(['id'=>intval($params['id'])])->update([$params['field']=>intval($params['state']), 'upd_time'=>time()]))
        {
            return DataReturn(MyLang('common.operate_success'), 0);
        }
        return DataReturn(MyLang('common.operate_fail'), -100);
    }

    /**
     * APP获取首页导航
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-11-19
     * @desc    description
     * @param   array           $params [description]
     */
    public static function AppHomeNav($params = [])
    {
        // 缓存
        $key = SystemService::CacheKey('shopxo.cache_app_home_navigation_key').APPLICATION_CLIENT_TYPE;
        $data = MyCache($key);
        if($data === null || MyEnv('app_debug'))
        {
            // 获取导航数据
            $field = 'id,name,images_url,event_value,event_type,bg_color,is_need_login';
            $order_by = 'sort asc,id asc';
            $data = Db::name('AppHomeNav')->field($field)->where(['platform'=>APPLICATION_CLIENT_TYPE, 'is_enable'=>1])->order($order_by)->select()->toArray();
            if(!empty($data))
            {
                foreach($data as &$v)
                {
                    // 图片地址
                    $v['images_url'] = ResourcesService::AttachmentPathViewHandle($v['images_url']);
                    $v['event_value'] = empty($v['event_value']) ? null : $v['event_value'];

                    // 事件值
                    if(!empty($v['event_value']))
                    {
                        // 地图
                        if($v['event_type'] == 3)
                        {
                            $v['event_value_data'] = explode('|', $v['event_value']);
                        }
                        $v['event_value'] = htmlspecialchars_decode($v['event_value']);
                    } else {
                        $v['event_value'] = null;
                    }
                }
            }

            // 手机首页导航钩子
            $hook_name = 'plugins_service_app_home_navigation_'.APPLICATION_CLIENT_TYPE;
            MyEventTrigger($hook_name, [
                'hook_name'     => $hook_name,
                'is_backend'    => true,
                'data'          => &$data,
            ]);

            // 没数据则赋空数组值
            if(empty($data))
            {
                $data = [];
            }

            // 存储缓存
            MyCache($key, $data, 180);
        }
        return $data;
    }
}
?>