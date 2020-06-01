<?php


namespace quansitech\dataGenerate;


interface Generator
{
    public static function add($data);
    public static function delete($map);
    public static function getOne($map);
}