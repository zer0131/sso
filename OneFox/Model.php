<?php

/**
 * @author: ryan<zer0131@vip.qq.com>
 * @desc: 基础Model类
 */

namespace OneFox;

abstract class Model {

    protected $db;
    protected $db_config = 'default';

    public function __construct() {
        $this->db = new DB($this->db_config);
    }

}

