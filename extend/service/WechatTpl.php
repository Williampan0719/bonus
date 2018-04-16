<?php
/**
 * Created by PhpStorm.
 * User: smallzz
 * Date: 2018/1/9
 * Time: 上午9:34
 */

namespace extend\service;

class WechatTpl
{
    /** 退款模版
     * auth smallzz
     * @param $param
     * @return array
     */
    public function refund($param)
    {
        $data = [
            'touser' => $param['openid'],#openid
            //'template_id' => '86d0CcmiB_FsAWD4Zx3ty9dOEBe_UbGk8eJMIkJGxb0',#模版id//赶紧说小程序
            //'template_id' => 'f9-BfqmW5r9J323JHpQDs3deVQCMGB3MIhgluO3NHZ4',#模版id//赶紧说小程序
            'template_id' => 'sDQkNLlK20SkqbHrtREhJmNMgPGfLKCB_CzTTEi3PqQ',
            'page' => $param['page'],#todo
            'form_id' => $param['form_id'],
            'data' => [

                'keyword1' => [
                    'value' => $param['key1'].'元',
                ],
                'keyword2' => [
                    'value' => '语音口令.'.$param['key2'].'未抢完',
                ],
                'keyword3' => [
                    'value' => $param['key3'] ?? '小程序账户余额',
                ],
                'keyword4' => [
                    'value' => $param['key4'] ?? '点击此处回听好友的语音',
                    'color' => '#f70707'
                ],

            ]
        ];
        return $data;
    }

    /**
     * @Author liyongchuan
     * @DateTime 2018-01-11
     *
     * @description 任务完成通知
     * @param $param
     * @return array
     */
    public function done($param)
    {
        $data = [
            'touser' => $param['openid'],#openid
            //'template_id' => 'vtNEgDYhRLK5z-sAUXddGfPhQLFPwR9mNHSoaWeHM2I',#模版id//赶紧说小程序
            //'template_id' => 'RjE0BdSkS3TzQ_R0xzLTL4UXzUTZ4pddezJactoJgrQ',#模版id//赶紧说小程序
            'template_id' => 'bX5EqIbY-ywieuxEBzTAQDRPIgfIkEP1JpFuMT1VPTU',
            'page' => $param['page'],#todo
            'form_id' => $param['form_id'],#formid
            'data' => [

                'keyword1' => [
                    'value' => '红包已经被领完',
                ],
                'keyword2' => [
                    'value' => $param['key2'],
                ],
                'keyword3' => [
                    'value' => '点击查看大家手气，回听好友录音',
                    'color' => '#f70707'
                ],
            ]

        ];
        return $data;
    }

    /**
     * @Author liyongchuan
     * @DateTime 2018-01-11
     *
     * @description 红包生成通知
     * @param $param
     * @return array
     */
    public function send($param)
    {
        $data = [
            'touser' => $param['openid'],#openid
            //'template_id' => 'CtidbrDrXFBy0Kjm2H7iwF36hPJgfnisFTg2wHwpGaU',#模版id//赶紧说小程序
            //'template_id' => 'z8jlDUbHl27wnYN99M6FAYlY0oKQm2L5TjPLfclzlUw',#模版id//赶紧说小程序
            'template_id'   => 'SA2ddULfh6EGmp6EgLNg0Q2oJ59-_o_00IwetrZGOGc',
            'page' => $param['page'],#todo
            'form_id' => $param['form_id'],#formid
            'data' => [
                'keyword1' => [
                    'value' => $param['key1'],
                ],
                'keyword2' => [
                    'value' => $param['key2'],
                ],
                'keyword3' => [
                    'value' => '点击此次查看语音口令详情',
                    'color' => '#f70707'
                ],
            ]

        ];
        return $data;
    }

    /**
     * @Author liyongchuan
     * @DateTime 2018-01-11
     *
     * @description 提现
     * @param $param
     * @return array
     */
    public function withdrawals($param)
    {
        $data = [
            'touser' => $param['openid'],#openid
            //'template_id' => 'rlEkfcvmDfODV8xgS4rDcUje0aCBsiaH8KPjMr0qC3g',#模版id
            //'template_id' => 'CTy8OtnjyZv2-vXIswoFdD-ECgQOPjmd9HkzWqeiH-A',#模版id//赶紧说小程序
            'template_id' => 'O2WBI_wHWL50_XHMsHQk3EmC-AszK1tw0BTWJdUgPdI',
            'page' => $param['page'],#todo
            'form_id' => $param['form_id'],#formid
            'data' => [
                'keyword1' => [
                    'value' => $param['key1'],//100.00元
                ],
                'keyword2' => [
                    'value' => $param['key2'] ?? '微信零钱',
                ],
                'keyword3' => [
                    'value' => $param['key3'],//时间,格式为2018-01-11 09:10:10
                ],
            ]

        ];
        return $data;
    }

    /**
     * @Author liyongchuan
     * @DateTime 2018-01-12
     *
     * @description 等级变化
     * @param $param
     * @return array
     */
    public function gradeChange($param)
    {
        $data = [
            'touser' => $param['openid'],#openid
            //'template_id' => '1rnz5q4pOKgWx3Dou6rSc87jhNv_e3LeISyVm_nB2zc',#模版id//赶紧说小程序
            //'template_id' => 'ulEDNQBCCOTAJNtyMvJiIQ1JdA52j0QXGgKXfVPwuIE',#模版id//赶紧说小程序
            'template_id' => 'RWPwBBAXm2WyE4HjYvbDKObUd_EhS9B8wwpJHcv8HIs',
            'page' => '',#todo
            'form_id' => $param['form_id'],#formid
            'data' => [
                'keyword1' => [
                    'value' => $param['key1'],//会员等级
                ],
                'keyword2' => [
                    'value' => $param['key2'],//原会员等级
                ],
                'keyword3' => [
                    'value' => '恭喜您晋升!截止目前您已推荐了'.$param['key3'].'位好友,推荐的好友数越多,等级越高',
                ],
            ]

        ];
        return $data;
    }

    /**
     * @Author liyongchuan
     * @DateTime 2018-01-12
     *
     * @description 每天11:30.推送给所有用户体力值
     * @param $param
     * @return array
     */
    public function give($param)
    {
        $data = [
            'touser' => $param['openid'],#openid
            //'template_id' => 'UcbuYJPwAvhRKhy48x_ByyEHvEDOzt7VoGKegqHSleY',#模版id//赶紧说
            //'template_id' => 'tFxN3UeQ6Vsq4YWRx2nFijcazqSWB-FS_3h72GLlUAs',#模版id//赶紧说
            'template_id' => 'jr0CFKPeRas9Fbose-IhQoeIskVcW0RhB1JqJGSQSgk',
            'page' => $param['page'],#todo
            'form_id' => $param['form_id'],#formid
            'data' => [
                'keyword1' => [
                    'value' => '体力值赠送',//体力值
                ],
                'keyword2' => [
                    'value' => '已赠送您'.$param['key2'].'体力值，快来领取吧！',//赠送的体力值
                    'color' => '#f70707'
                ],
            ]

        ];
        return $data;
    }

    /**
     * @Author liyongchuan
     * @DateTime 2018-01-18
     *
     * @description 分销提成
     * @param $param
     * @return array
     */
    public function distribution($param)
    {
        $data = [
            'touser' => $param['openid'],#openid
            //'template_id' => 'ouT8iZ5niwEjHHIbwsHzpwaV_lxJKVVquuEhuRoiUOY',#模版id//赶紧说
            //'template_id' => 'LZGhVtLOkwmfV9PCiZw5_NmahrveWodW-FlkGKa2uPg',#模版id//赶紧说
            'template_id' => 'L5XaqdtlX4sPtIhi8mvT76z6CPh64YYPB_fNo9H1Xlg',
            'page' => $param['page'],#todo
            'form_id' => $param['form_id'],#formid
            'data' => [
                'keyword1' => [
                    'value' => $param['key1'],
                ],
                'keyword2' => [
                    'value' => $param['key2'].'元',
                ],
                'keyword3' => [
                    'value' => $param['key3'],//赠送的体力值
                    'color' => '#f70707'
                ],
            ]

        ];
        return $data;
    }

    /**
     * @Author liyongchuan
     * @DateTime 2018-01-18
     *
     * @description 讨红包收入
     * @param $param
     * @return array
     */
    public function rewardMoney($param)
    {
        $data = [
            'touser' => $param['openid'],#openid
            //'template_id' => 'ouT8iZ5niwEjHHIbwsHzp-IBG0tGizqspOEsnqatnJA',#模版id//赶紧说
            //'template_id' => 'LZGhVtLOkwmfV9PCiZw5_I8M9tN-f5pEDYr7oiTj9lQ',#模版id//赶紧说
            'template_id' => 'L5XaqdtlX4sPtIhi8mvT77csS1k4SJfksPWldf0SuHM',
            'page' => $param['page'],#todo
            'form_id' => $param['form_id'],#formid
            'data' => [
                'keyword1' => [
                    'value' => '您发出的讨红包',
                ],
                'keyword2' => [
                    'value' => $param['key1'],
                ],
                'keyword3' => [
                    'value' => date('Y-m-d H:i:s'),
                ],
                'keyword4' => [
                    'value' => $param['key2'].'元',
                ],
                'keyword5' => [
                    'value' => $param['key3'],
                ],
            ]

        ];
        return $data;
    }

    /**
     * @Author panhao
     * @DateTime  2018-02-05
     *
     * @description 小游戏出结果模板
     * @param $param
     */
    public function gameResult($param)
    {
        $data = [
            'touser' => $param['openid'],#openid
            //'template_id' => 'ouT8iZ5niwEjHHIbwsHzp-IBG0tGizqspOEsnqatnJA',#模版id//赶紧说
            //'template_id' => 'LZGhVtLOkwmfV9PCiZw5_I8M9tN-f5pEDYr7oiTj9lQ',#模版id//赶紧说
            'template_id' => 'l1GU8UjFMaDKRzBnEnmjBCTod7oFONL9VdQheOZPqmA',
            'page' => $param['page'],#todo
            'form_id' => $param['form_id'],#formid
            'data' => [
                'keyword1' => [
                    'value' => '语音猜拳', // 挑战项目
                ],
                'keyword2' => [
                    'value' => $param['key1'], //挑战选手 a vs b
                ],
                'keyword3' => [
                    'value' => $param['key2'], // 挑战结果
                ],
                'keyword4' => [
                    'value' => $param['key3'].'金币', // 奖励信息
                ],
                'keyword5' => [
                    'value' => date('Y-m-d H:i:s'), // 挑战时间
                ],
            ]

        ];
        return $data;
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-07
     *
     * @description 小游戏无人应战退款
     * @param $param
     * @return array
     */
    public function gameRefund($param)
    {
        $data = [
            'touser' => $param['openid'],#openid
            'template_id' => 'sDQkNLlK20SkqbHrtREhJi6WzZWNpDx13Q7cizrl6Zw',#模版id//赶紧说呀
            'page' => $param['page'],#todo
            'form_id' => $param['form_id'],#formid
            'data' => [
                'keyword1' => [
                    'value' => '语音猜拳', // 挑战项目
                ],
                'keyword2' => [
                    'value' => $param['key1'], //押金
                ],
                'keyword3' => [
                    'value' => '由于长时间无人应战，挑战已过期，所扣金币已返还。', //备注
                ],
            ]

        ];
        return $data;
    }
}