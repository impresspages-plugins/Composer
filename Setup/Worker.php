<?php

namespace Plugin\Composer\Setup;


use Plugin\Composer\Model;

class Worker extends \Ip\SetupWorker
{

    public function activate() {
        $model = new Model();
        $model->init();
    }

    public function deactivate() {}

    public function remove() {}
}
