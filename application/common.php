<?php

use think\Db;

if (!function_exists('eval_conf')) {
    /**
     * 替换变量输出
     * @param $name
     * @param $data
     * @return bool|mixed|string
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    function eval_conf($name, $data)
    {
        $info = sysconf($name);
        extract($data);
        if ($info) {
            $info = str_replace('}', ' ', str_replace('{', ' $', $info));
            eval("\$info = \"$info\";");

            return $info;
        } else {
            return false;
        }
    }
}

if (!function_exists('dict_arr')) {
    /**
     * 字典类型获取
     * @param string $type
     * @return mixed
     */
    function dict_arr($type = '')
    {
        $list = Db::name('SystemDict')->where('type', $type)->column('name', 'value');

        return $list;
    }
}

if (!function_exists('dict_list')) {
    /**
     * 字典类型获取
     * @param string $type
     * @return array|PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    function dict_list($type = '')
    {
        $list = Db::name('SystemDict')->where('type', $type)->field('name,value')->select();

        return $list;
    }

}

if (!function_exists('pre')) {
    /**
     * 原样输出print_r的内容
     * @param string $content
     */
    function pre($content = '')
    {
        echo '<pre>' . print_r($content) . '</pre>';
    }
}

if (!function_exists('encrypt')) {
    /**
     * 密码加盐
     * @param string $data
     * @return string
     */
    function encrypt($data = '')
    {
        return md5(config('data_auth_key') . $data);
    }
}

if (!function_exists('format_byte')) {
    /**
     * 字节格式化
     * @param int $bytes
     * @return string
     */
    function format_byte($bytes = 0)
    {
        $size_text = array(' B', ' KB', ' MB', ' GB', ' TB', ' PB', ' EB', ' ZB', ' YB');

        return round($bytes / pow(1024, ($i = floor(log($bytes, 1024)))), 2) . $size_text[(int)$i];
    }
}