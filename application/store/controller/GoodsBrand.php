<?php

namespace app\store\controller;

use library\Controller;

/**
 * 品牌管理
 * Class GoodsBrand
 *
 * @package app\store\controller
 *
 * @author Leo.lei <346991581@qq.com>
 * @date 2019/5/12 20:27
 */
class GoodsBrand extends Controller
{
    /**
     * 绑定数据表
     * @var string
     */
    protected $table = 'StoreGoodsBrand';

    /**
     * 品牌管理
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function index()
    {
        $this->title = '品牌管理';
        $where = ['is_deleted' => '0'];
        $this->_query($this->table)->like('title')->equal('status')->where($where)->order('sort asc,id desc')->page();
    }


    /**
     * 新增品牌
     * @return mixed
     */
    public function add()
    {
        $this->title = '新增品牌';
        return $this->_form($this->table, 'form');
    }

    /**
     * 编辑品牌
     * @return mixed
     */
    public function edit()
    {
        $this->title = '编辑品牌';
        return $this->_form($this->table, 'form');
    }

    /**
     * 禁用品牌
     */
    public function forbid()
    {
        $this->_save($this->table, ['status' => '0']);
    }

    /**
     * 启用品牌
     */
    public function resume()
    {
        $this->_save($this->table, ['status' => '1']);
    }

    /**
     * 删除品牌
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function del()
    {
        $this->_delete($this->table);
    }
}