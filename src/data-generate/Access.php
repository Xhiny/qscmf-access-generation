<?php


namespace quansitech\dataGenerate;


use Illuminate\Support\Facades\DB;
use mysql_xdevapi\Exception;

class Access implements Generator
{
    public static $role_id;
    public static $tableName= 'qs_access';

    /**
     * 权限点设置
     * @param $data 二维数组需要添加权限点的数据
     * @param $role_id  用户组id
     * @param null $firstNode 模块名或模块id，默认为admin
     * @throws \Exception
     */
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
        try{
            DB::beginTransaction();
            /**
             * 创建模块节点
             */
            $nodeMap = Node::getMap(Node::$module, 0, 1);
            self::create($nodeMap);
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
     * @param $data 二维数组需要添加权限点的数据
     * @param $role_id  用户组id
     * @param null $firstNode 模块名或模块id，默认为admin
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
                // 删除权限点
                foreach ($value as $name=>$title){
                    $nodeMap = Node::getMap($name, $data['node_id'], 3);
                    $action = Node::getOne($nodeMap);
                    $access = self::getMapForData($action);
                    self::delete($access);
                }
                /**
                 * 查询是否还存在子节点，不存在删除控制器
                 */
                self::deleteParentNode($controller->id);
            }
            /**
             * 查询是否还存在子节点，不存在删除模块
             */
            self::deleteParentNode(Node::$firstId);
            DB::commit();
        }catch (\Exception $e){
            DB::rollBack();
            throw $e;
        }

    }

    /**
     * 根据id查询是否存在子节点权限点，不存在则删除
     * @param $nid
     * @throws \Exception
     */
    public static function deleteParentNode($nid){
        $node = Node::getOne(['id'=>$nid]);
        if (empty($node)) {
            throw new \Exception('node_id='.$nid.'is not exist!');
        }
        $ids = DB::table(Node::$tableName)->where('pid', $node->id)->pluck('id')->toArray();
        if (empty($ids)){
            $access = self::getMapForData($node);
            self::delete($access);
        } else {
            $data = DB::table(self::$tableName)->whereIn('node_id', $ids)->where('role_id', self::$role_id)->first();
            if (empty($data)){
                $access = self::getMapForData($node);
                self::delete($access);
            }
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