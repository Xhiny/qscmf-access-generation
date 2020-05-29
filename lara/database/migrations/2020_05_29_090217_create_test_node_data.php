<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTestNodeData extends Migration
{
    protected $nodeData = [
        'ControllerName1'=>[
            'testAction1' => '方法1',
            'testAction2' => '方法2',
            'testAction3' => '方法3',
        ],
        'ControllerName2'=>[
            'testAction1' => '方法1',
            'testAction2' => '方法2',
            'testAction3' => '方法3',
        ],
    ];
    
    public function up()
    {
        \quansitech\dataGenerate\Node::up($this->nodeData);
    }
    public function down()
    {
        \quansitech\dataGenerate\Node::down($this->nodeData);
    }
}
