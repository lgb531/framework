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

namespace app\store\controller\api\member;

use app\store\controller\api\Member;
use app\wechat\service\Wechat;
use library\tools\Data;
use think\Db;

/**
 * 订单接口管理
 * Class Order
 * @package app\store\controller\api\member
 */
class Order extends Member
{

    /**
     * 创建商城订单
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * 商品ID1@商品规格1@商品数量1||商品ID2@商品规格2@商品数量2
     */
    public function set()
    {
        $rule = $this->request->post('rule');
        if (empty($rule)) $this->error('下单商品规则不能为空！');
        list($order, $orderList) = [[], []];
        $order['mid'] = $this->member['id'];
        $order['order_no'] = Data::uniqidNumberCode(12);
        foreach (explode('||', $rule) as $item) {
            list($goods_id, $goods_spec, $number) = explode('@', $item);
            $map = ['id' => $goods_id, 'status' => '1', 'is_deleted' => '0'];
            $goods = Db::name('StoreGoods')->field('id,logo,title,image,content')->where($map)->find();
            if (empty($goods)) $this->error('查询商品主体信息失败，请稍候再试！');
            $goodsSpec = Db::name('StoreGoodsList')->where(['goods_id' => $goods_id, 'goods_spec' => $goods_spec])->find();
            if (empty($goodsSpec)) $this->error('查询商品规则信息失败，请稍候再试！');
            array_push($orderList, [
                'mid'           => $order['mid'],
                'order_no'      => $order['order_no'],
                'goods_id'      => $goods_id,
                'goods_spec'    => $goods_spec,
                'goods_logo'    => $goods['logo'],
                'goods_title'   => $goods['title'],
                'price_market'  => $goodsSpec['price_market'],
                'price_selling' => $goodsSpec['price_selling'],
                'price_member'  => $goodsSpec['price_member'],
                'price_express' => $goodsSpec['price_express'],
                'price_service' => $goodsSpec['price_service'],
                'price_real'    => $goodsSpec['price_member'] * $number,
                'number'        => $number,
            ]);
        }
        $order['status'] = '3';
        $order['price_goods'] = array_sum(array_column($orderList, 'price_real'));
        $order['price_express'] = array_sum(array_column($orderList, 'price_express'));
        $order['price_service'] = array_sum(array_column($orderList, 'price_service'));
        $order['price_total'] = $order['price_goods'] + $order['price_express'] + $order['price_service'];
        try {
            Db::name('StoreOrder')->insert($order);
            Db::name('StoreOrderList')->insertAll($orderList);
            $this->success('订单创建成功，请完成支付！', $this->getPayParams($order['order_no'], $order['price_total']));
        } catch (\think\exception\HttpResponseException $exception) {
            throw $exception;
        } catch (\Exception $e) {
            $this->error("创建订单失败，请稍候再试！{$e->getMessage()}");
        }
    }

    /**
     * 获取订单支付参数
     * @param string $order_no
     * @param string $pay_price
     * @return array
     */
    private function getPayParams($order_no, $pay_price)
    {
        $options = [
            'body'             => '商城订单支付',
            'openid'           => $this->openid,
            'out_trade_no'     => $order_no,
            'total_fee'        => '1',
            // 'total_fee'        => $pay_price * 100,
            'trade_type'       => 'JSAPI',
            'notify_url'       => url('@store/api.notify/wxpay', '', false, true),
            'spbill_create_ip' => $this->request->ip(),
        ];
        try {
            $pay = Wechat::WePayOrder(config('wechat.miniapp'));
            dump(config('wechat.miniapp'));
            $info = $pay->create($options);
            if ($info['return_code'] === 'SUCCESS' && $info['result_code'] === 'SUCCESS') {
                return $pay->jsapiParams($info['prepay_id']);
            }
            if (isset($info['err_code_des'])) throw new \think\Exception($info['err_code_des']);
        } catch (\Exception $e) {
            $this->error("创建订单失败参数失败！{$e->getMessage()}");
        }
    }

    /**
     * 获取订单列表
     */
    public function gets()
    {

    }

    /**
     * 获取订单信息
     */
    public function get()
    {

    }

    /**
     * 订单取消
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function cancel()
    {
        $where = [
            'mid'      => $this->member['id'],
            'order_no' => $this->request->post('order_no'),
        ];
        $order = Db::name('StoreOrder')->where($where)->find();
        if (empty($order)) $this->error('待取消的订单不存在，请稍候再试！');
        if (in_array($order['status'], ['1', '2'])) {
            $result = Db::name('StoreOrder')->where($where)->update([
                'status'       => '0',
                'cancel_state' => '1',
                'cancel_at'    => date('Y-m-d H:i:s'),
                'cancel_desc'  => '用户主动取消订单！',
            ]);
            if ($result !== false) $this->success('订单取消成功！');
            $this->error('订单取消失败，请稍候再试！');
        }
        $this->error('该订单状态不允许取消哦~');
    }

    /**
     * 订单确认收货
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function confirm()
    {
        $where = [
            'mid'      => $this->member['id'],
            'order_no' => $this->request->post('order_no'),
        ];
        $order = Db::name('StoreOrder')->where($where)->find();
        if (empty($order)) $this->error('待确认的订单不存在，请稍候再试！');
        if (in_array($order['status'], ['4'])) {
            $result = Db::name('StoreOrder')->where($where)->update(['status' => '5']);
            if ($result !== false) $this->success('订单确认成功！');
            $this->error('订单取确认失败，请稍候再试！');
        }
        $this->error('该订单状态不允许确认哦~');
    }
}