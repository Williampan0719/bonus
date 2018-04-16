<?php
/**
 * Created by PhpStorm.
 * User: smallzz
 * Date: 2018/1/24
 * Time: 上午9:18
 */

namespace extend\service;


use extend\helper\Curl;

class WechatCard extends WechatService
{
    /** 创建代金劵
     * auth smallzz
     * @param array $data [brand_name,code_type,title,color,notice,service_phone,description,quantity,accept_category,reject_category,least_cost,reduce_cost]
     * @return bool|mixed
     */
    public function createCard(){
        $config = config('card');
        $url = 'https://api.weixin.qq.com/card/create?access_token='.$this->getAToken();
        $data_info = [
            'card'=>[
                'card_type'=>$config['card_type'],
                'cash'=>[
                    'base_info'=>[
                        "logo_url"=>$config['logo_url'],
                        "brand_name"=>$config['brand_name'],
                        "code_type"=>$config['code_type'],
                        "title"=>$config['title'],
                        "color"=>$config['color'],
                        "notice"=>$config['notice'],
                        "description"=>$config['description'],
                        "date_info"=>[
                            "type"=>$config['type'],
                            "begin_timestamp"=>$config['begin_timestamp'],
                            "end_timestamp"=>$config['end_timestamp'],
                        ],
                        "sku"=> [
                            "quantity"=> $config['quantity'],
                        ],
                        'custom_url_name'=>$config['custom_url_name'],
                        'custom_url'=>$config['custom_url'],
                        'get_limit'=>$config['get_limit'],
                        'use_limit'=>$config['use_limit'],
                        'can_share'=>$config['can_share'],
                        'can_give_friend'=>$config['can_give_friend'],
                    ],
                    "advanced_info"=>[
                        "use_condition"=>[
                            "accept_category"=> $config['accept_category'],
                            "reject_category"=> $config['reject_category'],
                            "can_use_with_other_discount"=> $config['can_use_with_other_discount'],
                        ],
                    ],
                    "least_cost"=>$config['least_cost'],
                    "reduce_cost"=>$config['reduce_cost'],
                ]
            ]
        ];
        $result = Curl::postJson($url,json_encode($data_info,JSON_UNESCAPED_UNICODE));

        if(!empty($result['errcode'])){
            return false;
        }
        return ['result'=>$result,'data'=>$config];
    }
    /**
     * 上传图片，本接口所上传的图片不占用公众号的素材库中图片数量的5000个的限制。图片仅支持jpg/png格式，大小必须在1MB以下。
     * @return bool|array
     * http://mmbiz.qpic.cn/mmbiz_png/JicnmrhdyMFuWXFrG6WLAra2Iwbmiaz0m8aeGAEAA4fP4neSnIzJ2uE3vQE7R7GVOugr0K0DUFzjIl8epiaOl2STA/0
     */
    public function uploadImg()
    {

        if (!$this->getAToken() && !$this->getAToken()) {
            return false;
        }
        $filepath = '/home/srv/webroot/wechat-api-test/public/tmp/cardimg.png';
        if (class_exists ( '\CURLFile' )) {//关键是判断curlfile,官网推荐php5.5或更高的版本使用curlfile来实例文件
            $filedata = array (
                'buffer' => new \CURLFile ( realpath ( $filepath ), 'image/png' )
            );
        } else {
            $filedata = array (
                'buffer' => '@' . realpath ( $filepath )
            );
        }
        $url = 'https://api.weixin.qq.com/cgi-bin/media/uploadimg?access_token='.$this->getAToken();
        $result = Curl::upload($url,$filedata);
        if ($result) {
            $json = json_decode($result, true);
            if (!empty($json['errcode'])) {
                return false;
            }
            return $json['url'];
        }
        return false;
    }

    /**客服发送
     * auth smallzz
     */
    public function kfSend(){
        $url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token='.$this->getAToken();
        $data = [
            "touser"=>'',
            "msgtype"=>'wxcard',
            "wxcard"=>[
                'card_id'=>"pJTM6xA_asP3JgubMBJFuQYMnvI8"
            ],
        ];
        $result = Curl::postJson($url,json_encode($data));
        var_dump($result);
    }

    /** 升级卡卷小程序
     * auth smallzz
     * @return bool|mixed
     */
    public function cardSmall($card_id){
        $post = '{
                "card_id":"pJTM6xOuGd9RPteXbaImR7b8ya0w",
                "general_coupon": {
                        "base_info": {
                                "custom_url_name": "小程序",
                                "custom_url": "http://www.qq.com",
                                "custom_app_brand_user_name": "gh_05c38bf33b51@app",
                                "custom_app_brand_pass":"pages/index/index",
                                "custom_url_sub_title": "点击进入",
                                "promotion_url_name": "更多信息",
                                "promotion_url": "http://www.qq.com",
                                "promotion_app_brand_user_name": "gh_05c38bf33b51@app",
                                "promotion_app_brand_pass":"pages/index/index"
                        }
                }
        }';
        $url = "https://api.weixin.qq.com/card/update?access_token=".$this->getAToken();

        $result = Curl::postJson($url,$post);
        return $result;
    }

    /** 获取卡卷列表
     * auth smallzz
     * @return array|bool
     */
    public function getCard(){
        $url = 'https://api.weixin.qq.com/card/batchget?access_token='.$this->getAToken();
        $data = [
            "offset"=>0,
            "count"=>10,
        ];
        $result = Curl::postJson($url,json_encode($data));
        if($result['errcode'] == 0){
            return [
                'total_num'=>$result['total_num'],
                'card_id_list'=>$result['card_id_list'],
                'card_list'=>$result['card_list'],
            ];
        }
        return false;
    }
    public function getOpenidCard(){
        $url = 'https://api.weixin.qq.com/card/user/getcardlist?access_token='.$this->getAToken();
        $data = [
            "openid"=>"ok4Em0QT9D3sC0_3W1ZGnbaNBzgU",

        ];
        $result = Curl::postJson($url,json_encode($data));
        var_dump($result);
    }

    /** 解码code
     * auth smallzz
     * @param string $code
     * @return bool|mixed
     */
    public function deCode(string $code){
        $url = 'https://api.weixin.qq.com/card/code/decrypt?access_token='.$this->getAToken();
        $data['encrypt_code'] = $code;
        $result = Curl::postJson($url,json_encode($data));
        if($result['errcode'] == 0){
            return $result['code'];
        }
        return false;
    }

    /** 查询code接口
     * auth smallzz
     * @param string $card_id
     * @param string $code
     * @return bool|mixed
     */
    public function checkCode(string $card_id,string $code){
        $url = 'https://api.weixin.qq.com/card/code/get?access_token='.$this->getAToken();
        $data['card_id'] = $card_id;
        $data['code'] = $code;
        $result = Curl::postJson($url,json_encode($data));
        if($result['errcode'] == 0){
            return $result;
        }
        return false;
    }
    /** 卡劵核销
     * auth smallzz
     * @param string $card_id
     * @param string $code
     * @return bool|mixed
     */
    public function consume(string $card_id,string $code){
        $url = 'https://api.weixin.qq.com/card/code/consume?access_token='.$this->getAToken();
        $data['card_id'] = $card_id;
        $data['code'] = $code;
        $result = Curl::postJson($url,json_encode($data));
        if($result['errcode'] == 0){
            return $result;
        }
        return false;
    }

}