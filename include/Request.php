<?php

/*
 * Curl Helper
 * 
 * @author 第三方 by tianwei-iri@360.cn
 */

class Request
{

    public static function curl($url , $configs = array())
    {
        $b = microtime(true);
        $new_ch = curl_init();
        self::_setopt($new_ch , $url , $configs);
        $result = curl_exec($new_ch);
        $e = microtime(true);
        if (curl_errno($new_ch))
        {
            Logger::log('CULR_BAD\t' . "curl_errno:" . curl_errno($new_ch) . "\t" . curl_error($new_ch) . "\t" . ($e - $b) . "\t" . $url);
        }
        curl_close($new_ch);
        return $result;
    }

    public static function curlpost($url, $data, $configs = array()) 
    {
        $b = microtime(true);
        $new_ch = curl_init();
        self::_setopt($new_ch , $url , $configs);
        curl_setopt($new_ch, CURLOPT_POST, true);
        curl_setopt($new_ch, CURLOPT_POSTFIELDS, http_build_query($data));
        $result = curl_exec($new_ch);
        $e = microtime(true);
        if (curl_errno($new_ch))
        {
            Logger::log('CULR_BAD\t' . "curl_errno:" . curl_errno($new_ch) . "\t" . curl_error($new_ch) . "\t" . ($e - $b) . "\t" . $url.'\t'.json_encode($data));
        }
        curl_close($new_ch);
        return $result;
    }

    public static function multicurl($array)
    {
        $stime = microtime(true);
        $mh = curl_multi_init();
        foreach ($array as $urlinfo)
        {
            $url = isset($urlinfo['url']) ? $urlinfo['url'] : '';
            $new_ch = curl_init();
            self::_setopt($new_ch , $url);
            curl_multi_add_handle($mh , $new_ch);
        }
        $return = array();
        do
        {
            while (($code = curl_multi_exec($mh , $active)) == CURLM_CALL_MULTI_PERFORM);

            if ($code != CURLM_OK)
            {
                break;
            }

            while ($done = curl_multi_info_read($mh))
            {

                $info = curl_getinfo($done['handle']);
                $error = curl_error($done['handle']);
                $results = curl_multi_getcontent($done['handle']);

                $return[$info['url']] = self::_result($info , $error , $results , $stime);

                curl_multi_remove_handle($mh , $done['handle']);
                curl_close($done['handle']);
            }

            if ($active > 0)
            {
                curl_multi_select($mh , 0.1);
            }
        } while ($active);

        curl_multi_close($mh);
        return $return;
    }

    public static function _result($info , $error , $result , $stime = 0)
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        $utime = microtime(true) - $stime;
        $url = $info['url'];
        $total_time = $info['total_time'];
        $namelookup_time = $info['namelookup_time'];
        $connect_time = $info['connect_time'];
        $download_content_length = $info['download_content_length'];
        $pretransfer_time = $info['pretransfer_time'];
        $starttransfer_time = $info['starttransfer_time'];
        if ($error)
        {
            $msg = "used:{$utime}s\tip:{$ip}\turl:{$url}\tcurl_error:{$error}\ttotal_time:{$total_time}\tnamelookup_time:{$namelookup_time}\tconnect_time:{$connect_time}\tdownload_content_length:{$download_content_length}\tpretransfer_time:{$pretransfer_time}\tstarttransfer_time:{$starttransfer_time}";
            Logger::log("MULTI_CURL_BAD\t" . $msg);
        }
        if (empty($result))
        {
            return false;
        } else
        {
            return $result;
        }
    }

    public static function _setopt($new_ch , $url , $configs)
    {
        $timeout = isset($configs['timeout']) ? $configs['timeout'] : 10000;
        $header = isset($configs['header']) ? $configs['header'] : '';
        $cookie = isset($configs['cookie']) ? $configs['cookie'] : '';
        $refer = isset($configs['refer']) ? $configs['refer'] : '';
        $proxy = isset($configs['proxy']) ? $configs['proxy'] : '';
        $agent = isset($configs['agent']) ? $configs['agent'] : 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.0)';
        $post = isset($configs['post']) ? $configs['post'] : '';

        curl_setopt($new_ch , CURLOPT_URL , $url);
        curl_setopt($new_ch , CURLOPT_HTTP_VERSION , CURL_HTTP_VERSION_1_0);
        curl_setopt($new_ch , CURLOPT_USERAGENT , $agent);
        curl_setopt($new_ch , CURLOPT_TIMEOUT_MS , $timeout);
        curl_setopt($new_ch , CURLOPT_CONNECTTIMEOUT_MS , 10000);

        if (!empty($header) && is_array($header))
        {
            curl_setopt($new_ch , CURLOPT_HTTPHEADER , $header);
        }
        if (!empty($refer))
        {
            curl_setopt($new_ch , CURLOPT_REFERER , $refer);
        }
        if (!empty($proxy))
        {
            curl_setopt($new_ch , CURLOPT_PROXY , $proxy);
        }

        if (!empty($cookie))
        {
            curl_setopt($new_ch , CURLOPT_COOKIE , $cookie);
        }
        
        if (!empty($post))
        {
            curl_setopt($new_ch , CURLOPT_POST , true);
            curl_setopt($new_ch , CURLOPT_POSTFIELDS , $post);
            
        }
        curl_setopt($new_ch , CURLOPT_RETURNTRANSFER , true);
    }

}
