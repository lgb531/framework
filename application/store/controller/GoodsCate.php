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

namespace app\store\controller;

use library\Controller;
use library\tools\Data;
use think\Db;

/**
 * 商品分类管理
 * Class GoodsCate
 * @package app\store\controller
 */
class GoodsCate extends Controller
{
    /**
     * 绑定数据表
     * @var string
     */
    protected $table = 'StoreGoodsCate';

    /**
     * 商品分类管理
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function index()
    {
        $this->title = '商品分类管理';
        $where = ['is_deleted' => '0'];
        $this->_query($this->table)->like('title')->equal('status')->where($where)->order('sort asc,id desc')->page(false);
    }

    /**
     * 列表数据处理
     * @param array $data
     */
    protected function _index_page_filter(&$data)
    {
        foreach ($data as &$vo) {
            $vo['ids'] = join(',', Data::getArrSubIds($data, $vo['id']));
        }
        $data = Data::arr2table($data);
    }

    /**
     * 添加商品分类
     * @return mixed
     */
    public function add()
    {
        $this->title = '添加商品分类';
        return $this->_form($this->table, 'form');
    }

    /**
     * 编辑商品分类
     * @return mixed
     */
    public function edit()
    {
        $this->title = '编辑商品分类';
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
            // 上级菜单处理
            $_menus = Db::name($this->table)->where(['status' => '1', 'is_deleted' => 0, 'pid' => 0])->order('sort asc,id asc')->select();
            $_menus[] = ['title' => '顶级菜单', 'id' => '0', 'pid' => '-1'];
            $menus = Data::arr2table($_menus);
            foreach ($menus as $key => &$menu) if (substr_count($menu['path'], '-') > 3) unset($menus[$key]); # 移除三级以下的菜单
            elseif (isset($vo['pid']) && $vo['pid'] !== '' && $cur = "-{$vo['pid']}-{$vo['id']}") if (stripos("{$menu['path']}-", "{$cur}-") !== false || $menu['path'] === $cur) unset($menus[$key]); # 移除与自己相关联的菜单
            // 选择自己的上级菜单
            if (!isset($vo['pid']) && $this->request->get('pid', '0')) $vo['pid'] = $this->request->get('pid', '0');
            $this->menus = $menus;
        }
    }

    /**
     * 禁用商品分类
     */
    public function forbid()
    {
        $this->_save($this->table, ['status' => '0']);
    }

    /**
     * 启用商品分类
     */
    public function resume()
    {
        $this->_save($this->table, ['status' => '1']);
    }

    /**
     * 删除商品分类
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function del()
    {
        if (Db::name('StoreGoods')->where(['cate_id' => $this->request->post('id', null), 'is_deleted' => 0])->find()) {
            $this->error('此分类关联商品，不可删除！');
        }
        if (Db::name($this->table)->where(['pid' => $this->request->post('id', null), 'is_deleted' => 0])->count()) {
            $this->error('存在可用子分类，不可删除！');
        }
        $this->_delete($this->table);
    }

}