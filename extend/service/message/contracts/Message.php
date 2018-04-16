<?php
/**
 * Created by PhpStorm.
 * User: dongmingcui
 * Date: 2017/12/6
 * Time: 下午5:11
 */

namespace extend\service\message\contracts;

interface Message
{

    /**
     * Send a new message using
     * @param array $data
     * @param null $callback
     * @return mixed
     */
    public function send(array $data = [], $callback = null);

    /**
     *  Send group message
     * @param array $data
     * @param null $callback
     * @return mixed
     */
    public function sendAll(array $data = [], $callback = null);



}