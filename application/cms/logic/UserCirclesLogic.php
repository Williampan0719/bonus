<?php
/**
 * Created by PhpStorm.
 * User: panhao
 * Date: 2017/12/12
 * Time: 上午9:57
 * @introduce
 */
namespace app\cms\logic;

use app\cms\model\CirclesMessage;
use extend\helper\Utils;
use extend\service\FFmpegService;
use think\Exception;
use app\cms\model\Circles;
use app\cms\model\Comment;
use app\cms\model\CirclesLike;
use app\cms\model\CommentLike;
use app\user\model\User;
use app\system\model\SystemImage;
use app\common\logic\BaseLogic;
use think\db;

class UserCirclesLogic extends BaseLogic
{
    protected $circles;
    protected $comment;
    protected $like;
    protected $commentLike;
    protected $user;
    protected $image;
    protected $message;

    public function __construct()
    {
        $this->circles = new Circles();
        $this->comment = new Comment();
        $this->like = new CirclesLike();
        $this->commentLike = new CommentLike();
        $this->user = new User();
        $this->image = new SystemImage();
        $this->message = new CirclesMessage();
    }

    /**
     * @Author panhao
     * @DateTime 2017-12-27
     *
     * @description 视频截图
     * @param array $param
     * @return string
     */
    public function cutPic(array $param)
    {
        $ff = new FFmpegService();
        $data = $ff->cutPic($param['live_url']);
        $img = Utils::ossUpload([$data],'circles');
        return $img;
    }

    /**
     * @Author panhao
     * @DateTime 2017-12-11
     *
     * @description 获取全部
     * @param array $param
     * @return array
     */
    public function getCommunityList(array $param)
    {
        $param['page'] = $param['page'] ?? 1;
        $param['size'] = $param['size'] ?? 10;

        try {
            $list = $this->circles->getCommunityList($param);
            if (!empty($list)) {
                //动态用户头像昵称
                $user_uuids = array_column($list, 'user_uuid');
                $circles_users = $this->user->userNickList($user_uuids);
                $array = [];
                if (!empty($circles_users)) {
                    foreach ($circles_users as $key => $value) {
                        $array[$value['uuid']] = $value;
                    }
                }
                //取circles_uuid
                $ids = array_column($list, 'uuid');
                //取分组评论个数
                $comments = $this->comment->getCountByCids($ids);
                //批量判断是否已赞
                $likes = $this->like->getRowsByCids($ids, $param['user_uuid']);
                if (!empty($likes)) {
                    $likes = array_column($likes, 'circles_uuid');
                }
                //评论分组
                $comment_array = [];
                if (!empty($comments)) {
                    foreach ($comments as $key => $value) {
                        $comment_array[$value['circles_uuid']] = $value['count(*)'];
                    }
                }
                foreach ($list as $key => $value) {
                    //动态昵称
                    $list[$key]['nick_name'] = isset($array[$value['user_uuid']]) ? $array[$value['user_uuid']]['nick_name'] : '';
                    //动态头像
                    $list[$key]['portrait'] = isset($array[$value['user_uuid']]) ? $array[$value['user_uuid']]['portrait'] : '';
                    $list[$key]['has_liked'] = in_array($value['uuid'], $likes) ? 1 : 0;
                    $list[$key]['comment'] = $comment_array[$value['uuid']] ?? 0;
                    $list[$key]['img'] = $value['img'] ? config('oss.outer_host').$value['img'] : '';

                }
            }
            //动态顶部消息
            $where = ['to_user_uuid' => $param['user_uuid'],'is_read'=>0];
            $top['total'] = $this->message->getUnreadCount($where);
            if ($top['total']) {
                $user = $this->message->getNewOne($param['user_uuid']);
                $portrait = $this->user->userDetail($user['from_user_uuid']);
                $top['portrait'] = config('oss.outer_host') . $portrait['portrait'];
            } else {
                $top['portrait'] = '';
            }

            $result = $this->ajaxSuccess(202, ['top'=>$top,'list' => $list]);
        } catch (Exception $exception) {

            $result = $this->ajaxError(205);
        }
        return $result;
    }

    /**
     * @Author panhao
     * @DateTime 2018-1-3
     *
     * @description 动态详情
     * @param array $param
     * @return array
     */
    public function getCirclesDetail(array $param)
    {
        try {
            $detail = $this->circles->getCirclesDetail($param);
            if (!empty($detail)) {
                $detail['img'] = $detail['img'] ? config('oss.outer_host').$detail['img'] : '';
                $user_info = $this->user->userDetail($detail['user_uuid']);
                $detail['nick_name'] = $user_info['nick_name'] ?? '';
                $detail['portrait'] = $user_info['portrait'] ?? '';
                $comment = $this->comment->getRowsByCid($detail['uuid']);
                if (!empty($comment)) {
                    //批量判断是否已赞
                    $ids = array_column($comment, 'uuid');
                    $likes = $this->commentLike->getRowsByCid($ids, $param['user_uuid']);
                    if (!empty($likes)) {
                        $likes = array_column($likes, 'comment_uuid');
                    }
                    //评论用户头像昵称
                    $user_uuids = array_merge(array_column($comment, 'from_uuid'),array_column($comment, 'to_uuid'));
                    $comment_users = $this->user->userNickList($user_uuids);
                    $comment_array = [];
                    if (!empty($comment_users)) {
                        foreach ($comment_users as $key => $value) {
                            $comment_array[$value['uuid']] = $value;
                        }
                    }
                    foreach ($comment as $key => $value) {
                        //评论昵称
                        $comment[$key]['from_name'] = isset($comment_array[$value['from_uuid']]) ? $comment_array[$value['from_uuid']]['nick_name'] : '';
                        $comment[$key]['to_name'] = isset($comment_array[$value['to_uuid']]) ? $comment_array[$value['to_uuid']]['nick_name'] : '';
                        //评论头像
                        $comment[$key]['from_portrait'] = isset($comment_array[$value['from_uuid']]) ? $comment_array[$value['from_uuid']]['portrait'] : 0;
                        $comment[$key]['has_liked'] = in_array($value['uuid'], $likes) ? 1 : 0;
                    }
                }
                $detail['comment'] = $comment;
            }
            if ($detail) {
                $result = $this->ajaxSuccess(202, ['list' => $detail]);
            } else {
                $result = $this->ajaxError(205);
            }
        } catch (Exception $exception) {

            $result = $this->ajaxError(205);
        }
        return $result;
    }

    /**
     * @Author panhao
     * @DateTime 2018-1-3
     *
     * @description 发布动态
     * @param array $param
     * @return array
     */
    public function addCircles(array $param)
    {
        Db::startTrans();
        try {
            $param['uuid'] = Utils::genUUID('cir');
            //视频截图并上传
            if ($param['type'] == 3) {
                $ff = new FFmpegService();
                $cut = $ff->cutPic($param['live_url']);
                $img = Utils::ossUpload([$cut], 'circles');
                $param['img'] = $img[0];
            }
            //新增动态
            $data = $this->circles->addCircles($param);
            $data2 = 1;
            //批量插入图片
            if (!empty($param['img'])) {
                $info = [
                    'type' => 2,
                    'uuid' => Utils::genUUID('img'),
                    'img_path' => $param['img'],
                    'link_uuid'=> $param['uuid'],
                ];

                $data2 = $this->image->addImage($info);
            }
            if ($data && $data2) {
                Db::commit();
                $result = $this->ajaxSuccess(200, [], '发布成功');
            } else {
                Db::rollback();
                $result = $this->ajaxError(206);
            }

        } catch (Exception $exception) {

            Db::rollback();
            $result = $this->ajaxError(206);
        }

        return $result;
    }

    /**
     * 删除动态
     * @Author   panhao
     * @DateTime 2017-11-21T14:56:35+0800
     * @param    [string] uuid 动态uuid
     * @return   object
     */
    public function delCircles(array $param)
    {
        Db::startTrans();
        try {
            $data = $this->circles->delCircles(['uuid' => $param['uuid']]);

            $this->like->delCirclesLike(['circles_uuid' => $param['uuid']]);

            $this->image->delImage(['link_uuid' => $param['uuid']]);

            $comment_uuids = $this->comment->getRowsByCid($param['uuid']);
            $uuid = array_column($comment_uuids,'uuid');

            $this->comment->delComment(['circles_uuid' => $param['uuid']]);

            $this->commentLike->delCommentLike(['comment_uuid' => ['in',$uuid]]);

            if ($data) {
                Db::commit();
                $result = $this->ajaxSuccess(203);
            } else {
                Db::rollback();
                $result = $this->ajaxError(204);
            }
        } catch (Exception $exception) {
            Db::rollback();
            $result = $this->ajaxError(204);
        }

        return $result;
    }

    /**
     * @Author panhao
     * @DateTime 2017-12-11
     *
     * @description 动态点赞
     * @param array $param
     * @return array
     */
    public function addCirclesLike(array $param)
    {
        Db::startTrans();
        try {
            //判断用户是否已点赞
            $count = $this->like->isLiked($param['user_uuid'],$param['circles_uuid']);
            if ($count > 0) {
                return $this->ajaxError(206, [], '您已点赞');
            }
            //点赞表映射
            $data = $this->like->addCirclesLike($param);

            //动态liked+1
            $data2 = $this->circles->setLike(['uuid' => $param['circles_uuid']]);

            // 生成未读消息
            $to_uuid = $this->circles->getUserUuid(['uuid' => $param['circles_uuid']],'user_uuid');
            $where = ['from_user_uuid'=>$param['user_uuid'],'to_user_uuid'=>$to_uuid,'link_uuid'=>$param['circles_uuid'],'type'=>2];
            $message = $this->message->addMessage($where);

            if ($data && $data2 && $message) {
                Db::commit();
                $result = $this->ajaxSuccess(200, [], '点赞成功');
            } else {
                Db::rollback();
                $result = $this->ajaxError(206, [], '点赞失败');
            }
        } catch (Exception $exception) {
            Db::rollback();
            $result = $this->ajaxError(206, [], '点赞失败');
        }

        return $result;
    }

    /**
     * @Author panhao
     * @DateTime 2017-12-11
     *
     * @description 取消动态点赞
     * @param array $param
     * @return array
     */
    public function delCirclesLike(array $param)
    {
        Db::startTrans();
        try {
            $data = $this->like->delCirclesLike(['user_uuid'=> $param['user_uuid'],'circles_uuid'=>$param['circles_uuid']]);
            $data2 = $this->circles->unsetLike(['uuid' => $param['circles_uuid']]);
            // 生成未读消息
            $to_uuid = $this->circles->getUserUuid(['uuid' => $param['circles_uuid']],'user_uuid');
            $where = ['from_user_uuid'=>$param['user_uuid'],'to_user_uuid'=>$to_uuid,'link_uuid'=>$param['circles_uuid'],'type'=>2];
            $message = $this->message->delMessage($where);
            if ($data && $data2 && $message) {
                Db::commit();
                $result = $this->ajaxSuccess(203,[],'取消点赞成功');
            } else {
                Db::rollback();
                $result = $this->ajaxError(204,[],'取消点赞失败');
            }
        } catch (Exception $exception) {
            Db::rollback();
            $result = $this->ajaxError(204,[],'取消点赞失败');
        }

        return $result;
    }

    /**
     * @Author panhao
     * @DateTime 2018-1-3
     *
     * @description 新增评论
     * @param array $param
     * @return array
     */
    public function addComment(array $param)
    {
        Db::startTrans();
        try {
            $param['uuid'] = Utils::genUUID('com');
            $data = $this->comment->addComment($param);
            $message = 0;
            if ($param['type'] == 0) {
                // 生成未读消息
                $to_uuid = $this->circles->getUserUuid(['uuid' => $param['circles_uuid']],'user_uuid');
                $where = ['from_user_uuid'=>$param['from_uuid'],'to_user_uuid'=>$to_uuid,'link_uuid'=>$param['circles_uuid'],'content'=>$param['content'],'type'=>1];
                $message = $this->message->addMessage($where);
            } elseif ($param['type'] == 1) {
                // 生成未读消息
                $where = ['from_user_uuid'=>$param['from_uuid'],'to_user_uuid'=>$param['to_uuid'],'link_uuid'=>$param['circles_uuid'],'content'=>$param['content'],'type'=>1];
                $message = $this->message->addMessage($where);
            }

            if ($data && $message) {
                Db::commit();
                $result = $this->ajaxSuccess(200);
            } else {
                Db::rollback();
                $result = $this->ajaxError(206);
            }

        } catch (Exception $exception) {
            Db::rollback();
            $result = $this->ajaxError(206);
        }

        return $result;
    }

    /**
     * @Author panhao
     * @DateTime 2017-12-18
     *
     * @description 评论点赞
     * @param array $param
     * @return array
     */
    public function addCommentLike(array $param)
    {
        Db::startTrans();
        try {
            //判断用户是否已点赞
            $count = $this->commentLike->isLiked($param['user_uuid'], $param['comment_uuid']);
            if ($count > 0) {
                return $this->ajaxError(206, [], '您已点赞');
            }
            //点赞表映射
            $data = $this->commentLike->addCommentLike($param);
            //评论liked+1
            $data2 = $this->comment->setLike(['uuid' => $param['comment_uuid']]);

            // 生成未读消息
            $to_uuid = $this->comment->getUserUuid(['uuid' => $param['comment_uuid']],'from_uuid,circles_uuid');
            $where = ['from_user_uuid'=>$param['user_uuid'],'to_user_uuid'=>$to_uuid['from_uuid'],'link_uuid'=>$to_uuid['circles_uuid'],'type'=>2];
            $message = $this->message->addMessage($where);
            if ($data && $data2 && $message) {
                Db::commit();
                $result = $this->ajaxSuccess(200, [], '点赞成功');
            } else {
                Db::rollback();
                $result = $this->ajaxError(206, [], '点赞失败');
            }
        } catch (Exception $exception) {
            Db::rollback();
            $result = $this->ajaxError(206, [], '点赞失败');
        }

        return $result;
    }

    /**
     * @Author panhao
     * @DateTime 2017-12-18
     *
     * @description 评论取消点赞
     * @param array $param
     * @return array
     */
    public function delCommentLike(array $param)
    {
        Db::startTrans();
        try {
            //点赞表映射
            $data = $this->commentLike->delCommentLike(['user_uuid'=>$param['user_uuid'],'comment_uuid'=>$param['comment_uuid']]);
            //评论liked-1
            $data2 = $this->comment->unsetLike(['uuid' => $param['comment_uuid']]);
            // 生成未读消息
            $to_uuid = $this->comment->getUserUuid(['uuid' => $param['comment_uuid']],'from_uuid,circles_uuid');
            $where = ['from_user_uuid'=>$param['user_uuid'],'to_user_uuid'=>$to_uuid['from_uuid'],'link_uuid'=>$to_uuid['circles_uuid'],'type'=>2];
            $message = $this->message->delMessage($where);
            if ($data && $data2 && $message) {
                Db::commit();
                $result = $this->ajaxSuccess(203,[],'取消点赞成功');
            } else {
                Db::rollback();
                $result = $this->ajaxError(204,[],'取消点赞失败');
            }
        } catch (Exception $exception) {
            Db::rollback();
            $result = $this->ajaxError(204,[],'取消点赞失败');
        }

        return $result;
    }

    /**
     * @Author panhao
     * @DateTime 2017-12-28
     *
     * @description 消息列表
     * @param $param
     * @return array|int
     */
    public function myMessageList($param)
    {
        try {
            $where = ['to_user_uuid'=>$param['user_uuid']];
            if (isset($param['is_read']) && $param['is_read'] != '') {

                $where['is_read'] = $param['is_read'];
            }
            $list = $this->message->getRowsByUser($where);
            if (!empty($list)) {
                $user = array_column($list, 'from_user_uuid');
                $circles_users = $this->user->userNickList($user);
                //取昵称和头像
                $users = [];
                if (!empty($circles_users)) {
                    foreach ($circles_users as $key => $value) {
                        $users[$value['uuid']] = $value;
                    }
                }
                //取缩略图
                $img = array_column($list, 'link_uuid');
                $circles_imgs = $this->circles->getUserInfo(['uuid' => ['in', $img]], 'img,uuid');
                $imgs = [];
                if (!empty($circles_imgs)) {
                    foreach ($circles_imgs as $key => $value) {
                        $imgs[$value['uuid']] = $value;
                    }
                }
                foreach ($list as $key => $value) {
                    $list[$key]['nick_name'] = !empty($users[$value['from_user_uuid']]) ? $users[$value['from_user_uuid']]['nick_name'] : '';
                    $list[$key]['portrait'] = !empty($users[$value['from_user_uuid']]) ? $users[$value['from_user_uuid']]['portrait'] : '';
                    $list[$key]['img'] = !empty($imgs[$value['link_uuid']]) ? config('oss.outer_host') . $imgs[$value['link_uuid']]['img'] : '';
                }
                //标为已读
                $ids = array_column($list, 'id');
                $this->message->readMessage($ids);
            }
            $result = $this->ajaxSuccess(202,['list'=>$list]);
        } catch (Exception $exception) {
            $result = $this->ajaxError(205);
        }
        return $result;
    }

    /**
     * @Author panhao
     * @DateTime 2018-1-3
     *
     * @description 删除未读消息
     * @param $param
     * @return array
     */
    public function delMessage($param)
    {
        try {
            if ($param['type'] == 'all') {
                $list = $this->message->delMessage(['to_user_uuid'=> $param['user_uuid']]);
            } else {
                $list = $this->message->delMessage(['id'=> $param['id']]);
            }
            $result = $this->ajaxSuccess(103,['list'=>$list]);
        } catch (Exception $exception) {
            $result = $this->ajaxError(104);
        }
        return $result;
    }
}