<?php

namespace app\service;

use app\exception\WxException;
use app\model\User;
use curl\Curl;
use random\Random;

class WxPay
{
    // 报名付费
    public const ACTION_ENTER = '01';

    /**
     * 获得签名
     * @param array $param
     * @param string $encryption
     * @return string
     */
    public function generateSign(array $param, string $encryption = 'md5') : string
    {
        ksort($param);
        $str = '';
        foreach ($param as $key => $value) {
            if (!empty($value)) {
                $str .= "&{$key}={$value}";
            }
        }
        $mchKey = config('wx.mch_key');
        $str = substr($str, 1) . "&key=$mchKey";

        return hash($encryption, $str);
    }

    /**
     * 再次签名
     * @param int $prepayId
     * @return array
     */
    public function reSign(int $prepayId) : array
    {
        $random = Random::fixed(32)
            ->includeDigit()
            ->includeUpperLetters()
            ->includeLowerLetters()
            ->getString();

        $param = [
            'appId'     => config('wx.appid'),
            'timeStamp' => time(),
            'nonceStr'  => $random,
            'package'   => 'prepay_id=' . $prepayId,
            'signType'  => 'MD5'
        ];
        $param['sign'] = $this->generateSign($param);

        return $param;
    }

    /**
     * 获取prepay_id
     * @param array $order
     * @return string
     * @throws WxException
     */
    public function getPrepayId(array $order) : string
    {
        $url = config('wx.api.unified_order');
        $orderXml = Curl::arrayToXml($order);
        $return = Curl::post($url, $orderXml)->setOption(['return_form' => 'xml'])->execute();

        if (!$return || !isset($return['result_code'])) {
            throw (new WxException(110006))->setData($return);
        }

        if ($return['result_code'] == true && $return['return_code'] == true) {
            if (is_null($pi = $return['prepay_id']??null)) {
                throw (new WxException(110006))->setData($return);
            } else {
                return (string) $pi;
            }
        } else {
            throw (new WxException(110006))->setData($return);
        }
    }

    /**
     * 生成商户订单
     * @param $userId
     * @param string $action
     * @param int $fee
     * @param string $body
     * @param string $detail
     * @param string $attach
     * @return array
     */
    public function generateOrder(
        int $userId,
        string $action,
        int $fee,
        string $body,
        string $detail = '',
        string $attach = ''
    )
    {
        // 商品id = 时间戳(10) + 事件（2) + 随机数(4) = 16
        $orderId = sprintf(time() . $action . rand(1000, 9999));
        $openid = (new User)->getOpenid($userId);

        $random = Random::fixed(32)
            ->includeDigit()
            ->includeUpperLetters()
            ->includeLowerLetters()
            ->getString();
        $param = [
            'appid' => config('wx.appid'),
            // 商户号
            'mch_id' => config('wx.mch_id'),
            // 随机字符串
            'nonce_str' => $random,
            // 签名类型
            'sign_type' => 'MD5',
            // 商品描述(128)
            'body' => $body,
            // 商品详情(6000)
            'detail' => $detail,
            // 附加数据
            'attach' => $attach,
            // 商品订单号
            'out_trade_no' => $orderId,
            // 标价金额(分)
            'total_fee' => $fee,
            // 终端ip
            'spbill_create_ip' => config('server_ip'),
            // 回调地址
            'notify_url' => config('wx.callback.pay_notify'),
            // 交易类型
            'trade_type' => 'JSAPI',
            // 用户标识
            'openid' => $openid,
        ];

        $param['sign'] = $this->generateSign($param);
        return $param;
    }
}