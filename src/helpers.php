<?php
if (!function_exists('sync_format_keys')) {
    /**
     * 同步中间表时，格式化中间表额外的键
     * @param array $array 中间表关联键
     * @param array $attachArray 中间表额外字段
     * @return array
     */
    function sync_format_keys(array $array = [], array $attachArray = []): array
    {
        $newArray = [];
        foreach ($array as $k => $v) {
            $newArray[$v] = $attachArray;
        }
        return $newArray;
    }
}