<?php

namespace app\index\controller;

use think\Controller;

class Index extends Controller
{
    public function index()
    {
        return $this->fetch('reg');
    }

    public function notice()
    {
        return $this->fetch();
    }

    public function appDown()
    {
        return $this->fetch();
    }

    public function download()
    {
        return $this->fetch();
    }
}
