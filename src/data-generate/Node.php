<?php


namespace quansitech\dataGenerate;


use Illuminate\Support\Facades\DB;

class Node implements Generator
{
    // 设置最顶部(level=1)的node id值
    public static $firstId;
    public static $tableName= 'qs_node';
    public static $module = 'admin';
    /**
     * 生成节点数据，
     * @param $nodeData  二维数组
     * @param null $nodeNameOrId  string|int
     * @throws \Exception
     */
    public static function up($nodeData, $nodeNameOrId=null){
        self::setFirstNode($nodeNameOrId);
        if (empty(self::$firstId)){
            throw new \Exception('top node id  is not exist!');
        }
        try{
            DB::beginTransaction();
            foreach ($nodeData as $key => $value){
                 $map = self::getMap($key, self::$firstId, 2);
                 $controller = self::getOne($map);
                 if (empty($controller)){
                     $pid = self::add([
                         'name' => $key,
                         'title'=>$key,
                         'pid' => self::$firstId,
                         'level' => 2,
                         'status'=>1,
                     ]);
                 } else {
                     $pid = $controller->id;
                 }
                 if (empty($pid)){
                     throw new \Exception($key . ' controller is not exist and create node '. $key. ' fail!');
                 }
                // 新增action节点
                foreach ($value as $name=>$title){
                    $map = self::getMap($name, $pid, 3);
                    $action = self::getOne($map);
                    // 节点不存在就创建
                    if (empty($action)){
                        self::add([
                            'name'=>$name,
                            'title'=>$title,
                            'pid'=>$pid,
                            'level'=>3,
                            'status'=>1,
                        ]);
                    }
                }
            }
            DB::commit();
        }catch (\Exception $e){
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * 回滚
     * @param $nodeData
     * @param null $nodeNameOrId
     * @throws \Exception
     */
    public static function down($nodeData, $nodeNameOrId=null){
        self::setFirstNode($nodeNameOrId);
        if (empty(self::$firstId)){
            throw new \Exception('top node id  is not exist!');
        }
        try{
            DB::beginTransaction();
            foreach ($nodeData as $key => $value){
                $map = self::getMap($key, self::$firstId, 2);
                $controller = self::getOne($map);
                // 不存在则报错
                if(empty($controller)){
                    throw new \Exception('controller '. $key . ' is not exist!');
                }
                // 删除节点
                foreach ($value as $name => $title){
                    $map = self::getMap($name, $controller->id, 3);
                    $action = self::getOne($map);
                    if (empty($action)){
                        throw new \Exception($action . ' action is not exist and create node '. $action. ' fail!');
                    }
                    self::delete($map);
                }
                // 清除控制器
                if (!self::getOne(['pid'=>$controller->id])){
                    self::delete(['id'=>$controller->id]);
                }
            }
            DB::commit();
        }catch (\Exception $e){
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * 获取一条node值
     * @param $map  采用 thinkphp一样的条件值模式
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder|object|null
     */
    public static function getOne($map){
       $model = DB::table(self::$tableName);
       foreach ($map as $key => $value){
           if (is_array($value)){
               $model->where($key, $value[0], $value[1]);
           } else {
               $model->where($key, $value);
           }
       }
       return $model->first();
    }

    /**
     * 删除
     * @param $map
     * @return int
     */
    public static function delete($map){
        $model = DB::table(self::$tableName);
        foreach ($map as $key => $value){
            if (is_array($value)){
                $model->where($key, $value[0], $value[1]);
            } else {
                $model->where($key, $value);
            }
        }
        return $model->delete();
    }

    /**
     * 新增
     * @param $data
     * @return int
     */
    public static function add($data){
        return DB::table(self::$tableName)->insertGetId($data);
    }

    /**
     * 设置level=1的id值
     * @param null $firstId 可以是id值，也可以是name
     * @return int|mixed|string
     */
    public static function setFirstNode($firstId = null){
        if(is_numeric($firstId) && !empty($firstId) && $firstId > 0){
            $data = self::getOne(['id'=>$firstId]);
            self::$module = $data->name;
            self::$firstId = $firstId;
            return self::$firstId;
        }
        if (!empty($firstId) && is_string($firstId)){
            self::$module = $firstId;
            $map =[
                'name'=> $firstId,
                'level'=> 1,
            ];
            $admin = self::getOne($map);
            self::$firstId = $admin->id;
            return self::$firstId;
        }
        if (!empty(self::$firstId)){
            return self::$firstId;
        }
        $map =[
            'name'=> self::$module,
            'level'=> 1,
        ];
        $admin = self::getOne($map);
        self::$firstId = $admin->id;
        return self::$firstId;
    }

    /**
     * 生成map，主要是为了简化代码
     * @param $name
     * @param $pid
     * @param $level
     * @return array
     */
    public static function getMap($name, $pid, $level){
        return [
            'name' => $name,
            'pid' => $pid,
            'level' => $level,
        ];
    }
}