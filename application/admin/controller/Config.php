<?php

// +----------------------------------------------------------------------
// | framework
// +----------------------------------------------------------------------
// | 版权所有 2014~2018 广州楚才信息科技有限公司 [ http://www.cuci.cc ]
// +----------------------------------------------------------------------
// | 官方网站: http://framework.thinkadmin.top
// +----------------------------------------------------------------------
// | 开源协议 ( https://mit-license.org )
// +----------------------------------------------------------------------
// | github开源项目：https://github.com/zoujingli/framework
// +----------------------------------------------------------------------

namespace app\admin\controller;

use Exception;
use library\Controller;
use library\File;
use think\Db;
use think\exception\HttpResponseException;

/**
 * 系统参数配置
 * Class Config
 * @package app\admin\controller
 */
class Config extends Controller
{
    /**
     * 默认数据模型
     * @var string
     */
    protected $table = 'SystemConfig';

    /**
     *
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function index()
    {
        if ($this->request->isGet()) {
            $this->title = '系统参数配置';
            $this->_query($this->table)->like('title,name,group_id')->dateBetween('create_at')->order('sort asc,id asc')->page();
        }
    }

    /**
     * 数据列表处理
     * @param array $data
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function _index_page_filter(&$data)
    {
        $this->actions = dict_list('conf_group');
        $groupArr = dict_arr('conf_group');
        $typeArr = dict_arr('input_type');
        foreach ($data as &$vo) {
            $vo['conf_group'] = $groupArr[$vo['group_id']] ? $groupArr[$vo['group_id']] : $vo['group_id'];
            $vo['input_type'] = $typeArr[$vo['input_type']];
        }
    }

    /**
     * 添加配置
     */
    public function add()
    {
        $this->_form($this->table, 'form');
    }

    /**
     * 编辑配置
     */
    public function edit()
    {
        $this->_form($this->table, 'form');
    }

    /**
     * 表单数据处理
     * @param array $data
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function _form_filter(array $data)
    {
        if ($this->request->isGet()) {
            $this->assign('groups', dict_list('conf_group'));
            $this->assign('types', dict_list('input_type'));
            // 设置默认选中
            if (!isset($data['is_required'])) {
                $data['is_required'] = 1;
            }
        }
        if ($this->request->isPost()) {
            // 对开关的特殊处理
            if (!isset($data['is_required'])) {
                $data['is_required'] = 0;
            }
            // 备注支持html
            if (isset($data['remark'])) {
                $data['remark'] = htmlspecialchars_decode($data['remark']);
            }
        }
    }

    /**
     * 删除配置
     */
    public function del()
    {
        $this->_delete($this->table);
    }

    /**
     * 修改参数
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function setting()
    {
        $this->applyCsrfToken();
        if ($this->request->isGet()) {
            $this->title = Db::name('SystemDict')->where(['type' => 'conf_group', 'value' => $this->request->get('group_id')])->value('name');
            $this->list = $this->_query($this->table)->equal('group_id')->order('sort asc,id asc')->page();
            $this->fetch();
        } else {
            foreach ($this->request->post() as $k => $v) {
                if (is_array($v)) {
                    $v = join(',', $v);
                }
                sysconf($k, $v);
            }
            _syslog('系统管理', '系统参数配置成功');
            $this->success('系统参数配置成功！');
        }
    }

    /**
     * 文件存储配置
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function file()
    {
        $this->applyCsrfToken();
        if ($this->request->isGet()) {
            $this->fetch('file', [
                'title' => '文件存储配置',
                'point' => [
                    'oss-cn-hangzhou.aliyuncs.com' => '华东 1 杭州',
                    'oss-cn-shanghai.aliyuncs.com' => '华东 2 上海',
                    'oss-cn-qingdao.aliyuncs.com' => '华北 1 青岛',
                    'oss-cn-beijing.aliyuncs.com' => '华北 2 北京',
                    'oss-cn-zhangjiakou.aliyuncs.com' => '华北 3 张家口',
                    'oss-cn-huhehaote.aliyuncs.com' => '华北 5 呼和浩特',
                    'oss-cn-shenzhen.aliyuncs.com' => '华南 1 深圳',
                    'oss-cn-hongkong.aliyuncs.com' => '香港 1',
                    'oss-us-west-1.aliyuncs.com' => '美国西部 1 硅谷',
                    'oss-us-east-1.aliyuncs.com' => '美国东部 1 弗吉尼亚',
                    'oss-ap-southeast-1.aliyuncs.com' => '亚太东南 1 新加坡',
                    'oss-ap-southeast-2.aliyuncs.com' => '亚太东南 2 悉尼',
                    'oss-ap-southeast-3.aliyuncs.com' => '亚太东南 3 吉隆坡',
                    'oss-ap-southeast-5.aliyuncs.com' => '亚太东南 5 雅加达',
                    'oss-ap-northeast-1.aliyuncs.com' => '亚太东北 1 日本',
                    'oss-ap-south-1.aliyuncs.com' => '亚太南部 1 孟买',
                    'oss-eu-central-1.aliyuncs.com' => '欧洲中部 1 法兰克福',
                    'oss-eu-west-1.aliyuncs.com' => '英国 1 伦敦',
                    'oss-me-east-1.aliyuncs.com' => '中东东部 1 迪拜',
                ],
            ]);
        } else {
            $post = $this->request->post();
            if (isset($post['storage_type']) && $post['storage_type'] === 'local') {
                $exts = array_unique(explode(',', $post['storage_local_exts']));
                if (in_array('php', $exts)) $this->error('禁止上传可执行文件到本地服务器！');
                sort($exts);
                $post['storage_local_exts'] = join(',', $exts);
            }
            foreach ($post as $key => $value) sysconf($key, $value);
            if (isset($post['storage_type']) && $post['storage_type'] === 'oss') {
                try {
                    $local = sysconf('storage_oss_domain');
                    $bucket = $this->request->post('storage_oss_bucket');
                    $domain = File::instance('oss')->setBucket($bucket);
                    if (empty($local) || stripos($local, '.aliyuncs.com') !== false) {
                        sysconf('storage_oss_domain', $domain);
                    }
                    $this->success('阿里云OSS存储动态配置成功！');
                } catch (HttpResponseException $exception) {
                    throw $exception;
                } catch (Exception $e) {
                    $this->error("阿里云OSS存储配置失效，{$e->getMessage()}");
                }
            } else {
                $this->success('文件存储配置成功！');
            }
        }
    }

    /**
     * 数据库信息
     */
    public function data()
    {
        $tabs = Db::query('show table status');
        $total = 0;
        foreach ($tabs as $k => $v) {
            $tabs[$k]['size'] = format_byte($v['Data_length'] + $v['Index_length']);
            $total += $v['Data_length'] + $v['Index_length'];
        }

        $this->fetch('data', ['title' => '数据库信息', 'list' => $tabs, 'total' => format_byte($total), 'tables' => count($tabs)]);
    }

}