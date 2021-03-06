<?php
/** .-------------------------------------------------------------------
 * |  Software: [HDPHP framework]
 * |      Site: www.hdphp.com  www.hdcms.com
 * |-------------------------------------------------------------------
 * |    Author: 向军 <2300071698@qq.com>
 * |    WeChat: aihoudun
 * | Copyright (c) 2012-2019, www.houdunwang.com. All Rights Reserved.
 * '-------------------------------------------------------------------*/

namespace houdunwang\aliyunsms;

require_once __DIR__.'/aliyun/api_sdk/vendor/autoload.php';

use Aliyun\Core\Config as AliyunConfig;
use Aliyun\Core\Profile\DefaultProfile;
use Aliyun\Core\DefaultAcsClient;
use Aliyun\Api\Sms\Request\V20170525\SendSmsRequest;
use Aliyun\Api\Sms\Request\V20170525\QuerySendDetailsRequest;
use houdunwang\config\Config;

//加载区域结点配置
AliyunConfig::load();

/**
 * Class Base
 *
 * 短信服务API产品的DEMO程序,工程中包含了一个SmsDemo类，直接通过
 * 执行此文件即可体验语音服务产品API功能(只需要将AK替换成开通了云通信-短信服务产品功能的AK即可)
 * 备注:Demo工程编码采用UTF-8
 *
 * @package houdunwang\aliyunsms
 */
class Base
{
    static $acsClient = null;

    /**
     * 取得AcsClient
     *
     * @return null
     */
    public static function getAcsClient()
    {
        //产品名称:云通信流量服务API产品,开发者无需替换
        $product = "Dysmsapi";

        //产品域名,开发者无需替换
        $domain = "dysmsapi.aliyuncs.com";

        // TODO 此处需要替换成开发者自己的AK (https://ak-console.aliyun.com/)
        $accessKeyId = Config::get('aliyun.accessId'); // AccessKeyId

        $accessKeySecret = Config::get('aliyun.accessKey'); // AccessKeySecret

        // 暂时不支持多Region shanghai
        $region = Config::get('aliyun.regionId');
        // 服务结点
        $endPointName = Config::get('aliyun.regionId');

        if (static::$acsClient == null) {

            //初始化acsClient,暂不支持region化
            $profile = DefaultProfile::getProfile($region, $accessKeyId, $accessKeySecret);

            // 增加服务结点
            DefaultProfile::addEndpoint($endPointName, $region, $product, $domain);

            // 初始化AcsClient用于发起请求
            static::$acsClient = new DefaultAcsClient($profile);
        }

        return static::$acsClient;
    }

    /**
     * stdClass 转数组
     *
     * @param $array
     *
     * @return array
     */
    protected static function objectToArray($array)
    {
        if (is_object($array)) {
            $array = (array)$array;
        }
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                $array[$key] = self::objectToArray($value);
            }
        }

        return $array;
    }

    /**
     * 发送短信
     *
     * @param array $data
     *
     * @return mixed|\SimpleXMLElement
     */
    public static function send(array $data)
    {
        // 初始化SendSmsRequest实例用于设置发送短信的参数
        $request = new SendSmsRequest();

        // 必填，设置短信接收号码
        $request->setPhoneNumbers($data['mobile']);

        // 必填，设置签名名称，应严格按"签名名称"填写，请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/sign
        $request->setSignName($data['sign']);

        // 必填，设置模板CODE，应严格按"模板CODE"填写, 请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/template
        $request->setTemplateCode($data['template']);

        // 可选，设置模板参数, 假如模板中存在变量需要替换则为必填项
        $request->setTemplateParam(json_encode($data['vars'], JSON_UNESCAPED_UNICODE));

        // 可选，设置流水号
        $request->setOutId("yourOutId");

        // 选填，上行短信扩展码（扩展码字段控制在7位或以下，无特殊需求用户请忽略此字段）
        $request->setSmsUpExtendCode("1234567");

        // 发起访问请求
        $acsResponse = static::getAcsClient()->getAcsResponse($request);

        return self::objectToArray($acsResponse);
    }

    /**
     * 短信发送记录查询
     *
     * @return stdClass
     */
    public static function querySendDetails(array $data)
    {
        // 初始化QuerySendDetailsRequest实例用于设置短信查询的参数
        $request = new QuerySendDetailsRequest();
        // 必填，短信接收号码
        $request->setPhoneNumber("12345678901");
        // 必填，短信发送日期，格式Ymd，支持近30天记录查询
        $request->setSendDate("20170718");
        // 必填，分页大小
        $request->setPageSize(10);
        // 必填，当前页码
        $request->setCurrentPage(1);
        // 选填，短信发送流水号
        $request->setBizId("yourBizId");
        // 发起访问请求
        $acsResponse = static::getAcsClient()->getAcsResponse($request);

        return $acsResponse;
    }
}