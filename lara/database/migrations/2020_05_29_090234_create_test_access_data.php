<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTestAccessData extends Migration
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
        'NewAccess'=>[
            'access1' => '方法1',
            'access2' => '方法2',
            'access3' => '方法3',
        ],
    ];

    public function up()
    {
        \quansitech\dataGenerate\Access::up($this->nodeData, 1);
    }
    public function down()
    {
        \quansitech\dataGenerate\Access::down($this->nodeData, 1);
    }
}
