<?php

namespace app\controller\v1;

use app\api\BaseApi;
use app\model\Order;
use app\resource\WxQrcode;
use curl\Curl;
use app\exception\WxException;
use app\model\{User, Attendance};
use sap\Package;

/**
 * Class Wx
 * @package app\controller\v1
 */
class Wx extends BaseApi
{
    /**
     * 微信端获取令牌
     * @param string $code
     * @return Package|null
     * @throws WxException
     */
    public function getToken(string $code)
    {
        # 根据code获取微信后台给予的openid
        $param = [
            'appid'     => config('wx.appid'),
            'secret'    => config('wx.secret'),
            'js_code'   => $code,
            'grant_type'=> 'authorization_code'
        ];

        $result = Curl::get(config('wx.api.get_openid'), $param)->execute();

        if ($result === false) {
            throw new WxException(110001);
        }

        # 微信后台返回错误信息
        if (key_exists('errcode', $result)) {
            $e_data = [
                'code' => $code,
                'wx_result' => $result
            ];
            throw (new WxException(110002, ''))
                ->setData($e_data);
        }

//        $result = [
//            'openid' => 'oqLn64tF39vv3X24BtYGPu1tqPEM',
//            'session_key' => 'nmhl'
//        ];

        # 根据openid获取用户id
        $User = new User();
        $id = $User->getIdByOpenid($result['openid']);

        # 返回Token
        $payload = [
            'id' => $id,
            'sk' => $result['session_key']
        ];

        return $this
            ->setMethod('Token', 'get', 'v1')
            ->setParam(['payload' => $payload, 'permission' => 'USER'])
            ->call();
    }

    /**
     * 获取微信小程序二维码
     * @param string $scene
     * @param string $page
     * @return Package
     */
    public function getQRCode(string $scene, ?string $page = null) : Package
    {
        $accessToken = $this->getAccessToken();

        # 获取微信小程序二维码图片二进制流
        $url = config('wx.api.get_wx_qrcode');
        $post = [
            'scene' => $scene,
        ];
        if ($page) $post['page'] = (string)str_replace('.','/', $page);

        $file = Curl::post($url, $post, ['access_token' => $accessToken])->execute();

        # 微信后台返回错误
        if (is_array($file) && isset($file['errcode'])) {
            return Package::error(WxException::class, 110005)->setData($file);
        }

        # 将返回的二进制流转化为图片文件，保存在后台
        $Resource = new WxQrcode;
        $filename = md5(time() . mt_rand(10, 99)) . ".png";
        $filepath = $Resource->getDirPath() . $filename;
        $File = fopen($filepath, "w");
        fwrite($File, $file);
        fclose($File);

        return Package::ok('', ['path' => $Resource->getDomain() . $filename]);
    }

    /**
     *
     */
    public function PayNotifyCallback()
    {
        $xml = file_get_contents("php://input");
        $xml = xmlToArray($xml);
        $number = $xml['out_trade_no'];

        if (($xml['return_code']??false) == 'SUCCESS') {
            # 更新订单支付状态
            (new Order)->updateOrder($number);
            # 成功报名
            (new Attendance)->updatePaid($number);
        } else {
            # 支付失败
            echo 'error';
        }

        $response = [
            'return_code' => "<![CDATA[SUCCESS]]",
            "return_msg" => "<![CDATA[OK]]>"
        ];
        echo arrayToXml($response);
    }

    /**
     * 获取access_token
     * @return string|null
     */
    protected function getAccessToken() : ?string
    {
        $param = [
            'grant_type' => 'client_credential',
            'appid' => config('wx.appid'),
            'secret' => config('wx.secret'),
        ];

        $return = Curl::get(config('wx.api.get_access_token'), $param)->execute();

        if (isset($return['errcode'])) {
            return Package::error(WxException::class, 110004)->setData($return);
        }

        return $return['access_token'];
    }
}