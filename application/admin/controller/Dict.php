<?php

namespace app\admin\controller;

use library\Controller;
use think\Db;

/**
 * 字典配置
 * Class Dict
 *
 * @package app\admin\controller
 *
 * @author Leo.lei <346991581@qq.com>
 * @date 2019/5/12 02:45
 */
class Dict extends Controller
{
    /**
     * 默认数据模型
     * @var string
     */
    protected $table = 'SystemDict';

    /**
     * 参数字典
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function index()
    {
        $this->assign('actions', Db::name($this->table)->group('type')->column('type'));
        if ($this->request->isGet()) {
            $this->title = '系统参数字典';
            $this->_query($this->table)->like('name,type')->dateBetween('create_at')->order('type asc,value asc,id desc')->page();
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
        $this->actions = Db::name($this->table)->group('type')->column('type');
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
     * 删除配置
     */
    public function del()
    {
        $this->_delete($this->table);
    }

    /**
     * 启用字典
     */
    public function resume()
    {
        $this->applyCsrfToken();
        $this->_save($this->table, ['status' => '1']);
    }

    /**
     * 禁用字典
     */
    public function forbid()
    {
        $this->applyCsrfToken();
        $this->_save($this->table, ['status' => '0']);
    }
}