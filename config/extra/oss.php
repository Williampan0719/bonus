<?php
/**
 * Created by PhpStorm.
 * User: zzhh
 * Date: 2017/12/6
 * Time: 下午4:28
 */
return [
    'user' => 'pgyapi',
    'access_key' => 'LTAIHvMFiHyWjGan',
    'access_secret_key' => 'iwNdU1WR4oEhbDcyD4MeaKBWCKmQY3',
    'default_bucket_name' => 'pgy-hongbao', # 默认的bucket
    'outer_endpoint' => 'oss-cn-beijing.aliyuncs.com',# 外网endpoint
    'outer_host' => 'http://pgy-hongbao.oss-cn-beijing.aliyuncs.com/', # 外网访问域名，注意要以'/'结尾
    'inner_endpoint' => 'oss-cn-beijing-internal.aliyuncs.com', # 内网endpoint
    'inner_host' => 'http://pgy-hongbao.oss-cn-beijing-internal.aliyuncs.com/',# 内网访问域名
    'temp_file_path' => 'tmp',#临时文件路径
    'temp_file_suffix' => '.mp3',#临时文件后缀
    'temp_pic_suffix'  => '.jpg', // 临时文件后缀
    'oss_dir' => 'audio/',#临时文件后缀
    'oss_dir_code' => 'code/'#临时文件后缀
];