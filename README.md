# quansitech/qscmf-access-generation
![Travis (.com)](https://img.shields.io/travis/com/tiderjian/lara-for-tp.svg?style=flat-square)
![style ci](https://img.shields.io/travis/com/tiderjian/lara-for-tp.svg?style=flat-square)
![download](https://img.shields.io/packagist/dt/tiderjian/lara-for-tp.svg?style=flat-square)
![lincense](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)
[![LICENSE](https://img.shields.io/badge/license-Anti%20996-blue.svg)](https://github.com/996icu/996.ICU/blob/master/LICENSE)
![Pull request welcome](https://img.shields.io/badge/pr-welcome-green.svg?style=flat-square)

## 介绍
由于手动添加权限点比较繁琐，并且容易出错，所有寄希望于脚本来简化操作。该功能主要实现权限点添加，额外实现qs_node节点添加
   

## 安装
安装[qs_cmf](https://github.com/tiderjian/qs_cmf)

composer安装
```
composer require quansitech/qscmf-access-generation
```


## 使用
目前支持的laravel功能有 migrate、make:model、make:seeder、db:seed，具体用法请自行查阅laravel手册。

migrate文件必须存放在lara/database/migrations下,在lara目录下的.env文件中配置要访问的数据库,然后在项目根目录执行php artisan migrate即可完成数据库的迁移，相关的migrate命令可查看[laravel文档](https://learnku.com/docs/laravel/5.8/migrations/3928)。

测试脚本必须存放到lara/tests路径下，继承该目录下的TestCase类。配置phpunit.xml文件，设置可用于测试使用的数据库及web服务地址端口。最后运行phpunit，执行测试脚本。关于laravel dusk的使用请查阅[laravel文档](https://learnku.com/docs/laravel/5.8/dusk/3943)。

## 文档
### Node
用于生成qs_node节点数据，并且已存在的节点程序不会再创建
#### 使用
请参考lara\database\migrations\2020_05_29_090217_create_test_node_data.php文件
```
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

``` 
#### Node:up、Node:down方法参数说明

##### 参数 $nodeData 必填

$nodeData参数是一个二维数组格式如下：
 ```  
$nodeData = [
        '控制器名'=>[
            '方法名' => '方法中文名称',
            ......
        ],
        ......
    ];
 ```
##### 参数 $nodeNameOrId 选填

说明：模块名：默认为admin，可选填qs_node中level=1的id或name的值
如默认的amdin模块id=1
  

### Access
用于生成qs_access权限点数据
```
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
```
#### Access:up、Access:down方法参数说明

##### 参数 $data必填
 
$nodeData参数是一个二维数组格式如下：
 ``` 
$nodeData = [
        '控制器名'=>[
            '方法名' => '方法中文名称',
            ......
        ],
        ......
    ];  
``` 
##### 参数 $role_id 必填

说明：用户组id值

##### 参数 $firstNode  选填 

说明：模块名：默认为admin，可选填qs_node中level=1的id或name的值如默认的amdin模块id=1