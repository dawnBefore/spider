<?php

class Utils
{
    public static function crawlContent($url, $encode = true) {
        $file_name = '../cache/'.md5($url);
        if (!file_exists($file_name)) {
            @touch($file_name);
        }
        $content = file_get_contents($file_name);
        if (empty($content)) {
            $content = Request::curl($url);
            if (empty($content)) {
                sleep(1);
                $content = Request::curl($url);
            }
            $encode && $content = iconv("GBK", "UTF-8//IGNORE",$content);
            file_put_contents($file_name, $content);
        }
        return $content;
    }
}
