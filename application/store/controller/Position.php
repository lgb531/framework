<?php

namespace app\store\controller;

use library\Controller;
use think\Db;

/**
 * 广告位管理
 * Class Position
 *
 * @package app\store\controller
 *
 * @author Leo.lei <346991581@qq.com>
 * @date 2019/5/12 20:44
 */
class Position extends Controller
{
    /**
     * 绑定数据表
     * @var string
     */
    protected $table = 'StorePosition';

    /**
     * 广告位管理
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function index()
    {
        $this->title = '广告位管理';
        $where = ['is_deleted' => '0'];
        $this->_query($this->table)->like('title')->equal('status,position_type')->where($where)->order('position_type asc,sort asc,id desc')->page();
    }

    /**
     * 数据列表处理
     * @param $data
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function _index_page_filter(&$data)
    {
        $this->actions = dict_list('position_type');
        $positionArr = dict_arr('position_type');
        $linkArr = dict_arr('link_type');
        foreach ($data as &$vo) {
            $vo['position_type'] = $positionArr[$vo['position_type']];
            $vo['link_type'] = $linkArr[$vo['link_type']];
        }
    }

    /**
     * 添加广告
     * @return mixed
     */
    public function add()
    {
        $this->title = '添加广告';
        return $this->_form($this->table, 'form');
    }

    /**
     * 编辑广告
     * @return mixed
     */
    public function edit()
    {
        $this->title = '编辑广告';
        return $this->_form($this->table, 'form');
    }

    /**
     * 表单数据处理
     * @param array $vo
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function _form_filter(array $vo)
    {
        if ($this->request->isGet()) {
            $this->assign('positions', dict_list('position_type'));
            $this->assign('links', dict_list('link_type'));
            $this->assign('cates', Db::name('StoreGoodsCate')->where(['is_deleted' => 0, 'pid' => 0])->field('id as value,title as name')->select());
        }
        if ($this->request->isPost()) {
            if (!$vo['position_type']) {
                $this->error('请选择广告位');
            }
            if ('7' == $vo['position_type'] && '' == $vo['cate_id']) {
                $this->error('请选择商品分类');
            }
            if (!$vo['logo']) {
                $this->error('请上传图片');
            }
        }
    }

    /**
     * 禁用广告
     */
    public function forbid()
    {
        $this->_save($this->table, ['status' => '0']);
    }

    /**
     * 启用广告
     */
    public function resume()
    {
        $this->_save($this->table, ['status' => '1']);
    }

    /**
     * 删除广告
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function del()
    {
        $this->_delete($this->table);
    }
}