<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/14
 * Time: 2:10
 */

namespace app\admin\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;

class Hello extends Command {
    protected function configure() {
        $this->setName('hello')
            ->setDescription('测试命令啊');
    }

    protected function execute(Input $input, Output $output) {
        P('测试执行命令！');
    }
}