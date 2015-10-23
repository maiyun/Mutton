<?php
/**
 * Created by PhpStorm.
 * User: Admin2
 * Date: 2015/5/25
 * Time: 19:56
 */

namespace Chameleon\Library;

/*
 * 若使用 Db 模式，则需要手动载入 Db 类
 * 若使用 Memcached 模式，则需要手动载入 Memcached 类
 */

class Session {

    var $key = '';

    // --- 可编辑变量 ---

    var $exp = 432000; // --- 5 天 ---
    var $source = NULL;
    var $cookie = 'SESSIONKEY';
    var $code = '';
    var $first = false;

    function __construct() {



    }

    function __destruct() {

        if($this->source instanceof Db)
            $this->source->query('UPDATE `' . $this->source->pre . 'session` SET `data` = "' . $this->source->escape(serialize($_SESSION)) . '",`time` = "' . time() . '" WHERE `key` = "' . $this->key . '"');
        else if($this->source instanceof Memcached) {
            $_SESSION['sess']['time'] = time();
            $this->source->set('sess_'.$this->key, $_SESSION, $this->exp);
        }

    }

    function gc() {

        if($this->source instanceof Db)
            L()->Db->query('DELETE'.' FROM `' . $this->source->pre . 'session` WHERE `time` < "'.(time() - $this->exp).'"');

    }

    function start() {

        if(isset($_POST[$this->cookie]) && $_POST[$this->cookie]) $this->key = $_POST[$this->cookie];
        else if(isset($_COOKIE[$this->cookie]) && $_COOKIE[$this->cookie]) $this->key = $_COOKIE[$this->cookie];
        if(!ctype_alnum($this->key)) $this->key = '';

        $_SESSION = [];
        if($this->source instanceof Db) {
            if($this->source->isConnected()) {
                if($this->key != '')
                    $r = $this->source->query('SELECT' . ' * FROM `' . $this->source->pre . 'session` WHERE `key` = "' . $this->key . '";');
                // --- 影响 0 行代表之前没有 Session ---
                if ($this->key =='' || $this->source->getAffectRows() == 0) {
                    $this->key = date('Ymd') . $this->random();
                    $time = time();
                    $this->code = $this->random();
                    $this->first = true;
                    while (!$this->source->query('INSERT' . ' INTO `' . $this->source->pre . 'session` (`key`,`data`,`time`,`time_add`, `code`) VALUES ("' . $this->key . '","a:0:{}","' . $time . '","' . $time . '", "'.$this->code.'")', false))
                        $this->key = date('Ymd') . $this->random();
                } else {
                    $s = $r->fetch_assoc();
                    if ($s['time_add'] < time() - 10800) {
                        // --- 进行 Session KEY 置换 ---
                        $this->key = date('Ymd') . $this->random();
                        $time = time();
                        $this->code = $s['code'];
                        while (!$this->source->query('INSERT'.' INTO `' . $this->source->pre . 'session` (`key`,`data`,`time`,`time_add`, `code`) VALUES ("' . $this->key . '","' . $this->source->escape(serialize($_SESSION)) . '","' . $time . '","' . $time . '", "' . $this->code . '")', false))
                            $this->key = date('Ymd') . $this->random();
                        $this->source->query('DELETE'.' FROM `' . $this->source->pre . 'session` WHERE `key` = "'.$s['key'].'"');
                    }
                    $_SESSION = unserialize($s['data']);
                }
                if(!isset($_POST[$this->cookie])) setcookie($this->cookie, $this->key, time() + $this->exp, '/');
            } else
                logs('L(Session)', 'Db not connect', true);
        } else if($this->source instanceof Memcached) {
            if($this->source->isConnect()) {
                $s = $this->source->get('sess_'.$this->key);
                if($s === false) {
                    // --- 没有 session ---
                    $this->key = date('Ymd') . $this->random();
                    $time = time();
                    $this->code = $this->random();
                    $this->first = true;
                    $_SESSION['sess'] = [
                        'time' => $time,
                        'time_add' => $time,
                        'code' => $this->code
                    ];
                    while($this->source->get('sess_'.$this->key) !== false)
                        $this->key = date('Ymd') . $this->random();
                    $this->source->set('sess_'.$this->key, $_SESSION, $this->exp);
                } else {
                    // --- $s 是 session 的信息，此时只有 $this->key 被赋值了 ---
                    if ($s['sess']['time_add'] < time() - 10800) {
                        // --- 进行 Session KEY 置换 ---
                        $oldKey = $this->key;
                        $this->key = date('Ymd') . $this->random();
                        $time = time();
                        while ($this->source->get('sess_'.$this->key) !== false)
                            $this->key = date('Ymd') . $this->random();
                        $s['sess'] = [
                            'time' => $time,
                            'time_add' => $time,
                            'code' => $s['sess']['code']
                        ];
                        $this->source->set('sess_'.$this->key, $s, $this->exp);
                        $this->source->delete('sess_'.$oldKey);
                    }
                    $_SESSION = $s;
                }
                if(!isset($_POST[$this->cookie])) setcookie($this->cookie, $this->key, time() + $this->exp, '/');
            } else
                logs('L(Session)', 'Memcached not connect');
        } else
            logs('L(Session)', 'Please set source first');

    }

    protected function random() {
        $s = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $sl= strlen($s);
        $t = '';
        for ($i = 8; $i; $i--)
            $t .= $s[rand(0, $sl - 1)];
        return $t;
    }

}