<?php


namespace quansitech\dataGenerate;


interface Generator
{
    public static function up($arr, $id);
    public static function down($arr, $id);
    public static function add($data);
    public static function delete($map);
    public static function getOne($map);
}