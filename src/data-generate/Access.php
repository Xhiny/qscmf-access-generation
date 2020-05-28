<?php


namespace quansitech\dataGenerate;


use Illuminate\Support\Facades\DB;

class Access implements Generator
{
    public static $role_id;
    public static $tableName= 'qs_access';

    public static function up($data, $role_id, $firstNode=null)
    {
        if (!empty($role_id)){
            self::$role_id = $role_id;
        }
        if (empty(self::$role_id)){
            throw new \Exception('role_id must!');
        }
        // 注意这里新增的节点不会进行回滚操作，已存在的节点也不会创建
        Node::up($data, $firstNode);
        /**
         * 创建 $firstNode控制节点
         */
        try{
            DB::beginTransaction();
            foreach ($data as $key => $value){
                $nodeMap = Node::getMap($key, Node::$firstId, 2);
                $controller = self::create($nodeMap);
                // 新增权限点
                foreach ($value as $name=>$title){
                    $nodeMap = Node::getMap($name, $controller['node_id'], 3);
                    self::create($nodeMap);
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
     * @param $data
     * @param $role_id
     * @param null $firstNode
     * @throws \Exception
     */
    public static function down($data, $role_id, $firstNode=null)
    {
        if (!empty($role_id)){
            self::$role_id = $role_id;
        }
        if (empty(self::$role_id)){
            throw new \Exception('role_id must!');
        }
        Node::setFirstNode($firstNode);
        try{
            DB::beginTransaction();
            foreach ($data as $key => $value){
                $nodeMap = Node::getMap($key, Node::$firstId, 2);
                $controller = Node::getOne($nodeMap);
                $data = self::getMapForData($controller);
                // 新增权限点
                foreach ($value as $name=>$title){
                    $nodeMap = Node::getMap($name, $data['node_id'], 3);
                    $action = Node::getOne($nodeMap);
                    $access = self::getMapForData($action);
                    self::delete($access);
                }
                /**
                 * 查询是否还存在子节点，不存在删除控制器
                 */
            }
            DB::commit();
        }catch (\Exception $e){
            DB::rollBack();
            throw $e;
        }

    }

    /**
     * 创建access数据
     * @param $map
     * @return array
     * @throws \Exception
     */
    public static function create($map){
        $controller = Node::getOne($map);
        $data = self::getMapForData($controller);
        $access = self::getOne($data);
        // 权限点不存在就创建
        if (empty($access)){
            self::add($data);
        }
        return $data;
    }

    /**
     * 获取预设的access数据格式，主要是为了简化代码
     * @param $node_id
     * @param $level
     * @param $module
     * @return array
     */
    public static function getMap($node_id, $level, $module){
        return [
            'role_id' => self::$role_id,
            'node_id' => $node_id,
            'level' => $level,
            'module' => $module
        ];
    }
    public static function getMapForData($data){
        if (empty($data)){
            throw new \Exception('getMapForData  param data null');
        }
        return [
            'role_id' => self::$role_id,
            'node_id' => $data->id,
            'level' => $data->level,
            'module' => $data->name
        ];
    }

        /**
     * 新增
     * @param $data
     * @return int
     */
    public static function add($data){
        return DB::table(self::$tableName)->insert($data);
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

}