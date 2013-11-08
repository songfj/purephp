<?php

class pure_social
{

    const FACEBOOK_SHARE_BASE_URL = 'http://www.facebook.com/sharer/sharer.php';
    const TWITTER_SHARE_BASE_URL = 'http://twitter.com/intent/tweet';
    const GOOGLEPLUS_SHARE_BASE_URL = 'https://plus.google.com/share';

    public static function facebook_share_url($url, $title = '', $images = array(), $summary = '', $s = 100)
    {
        //http://www.facebook.com/sharer/sharer.php?s=100&p[url]=myurl&p[images][0]=myimage.png&p[title]=mytitle&p[summary]=mysummary

        $p = array(
            "url" => ($url)
        );
        if (!empty($title)) {
            $p['title'] = ($title);
        }
        if (!empty($images)) {
            $images = is_array($images) ? $images : array($images);
            foreach ($images as $i => $im) {
                $images[$i] = ($im);
            }
            $p['images'] = $images;
        }
        if (!empty($summary)) {
            $p['summary'] = $summary;
        }

        return self::FACEBOOK_SHARE_BASE_URL . '?' . http_build_query(array("s" => $s, "p" => $p));
    }

    public static function twitter_share_url($text, $url = '', $via = '')
    {
        //http://twitter.com/intent/tweet?text=The+Inconvenient+Truth+of+Education+%22Reform%22%3A+http%3A%2F%2Fbit.ly%2F12nPcP2+%28via+%40otlcampaign%29

        $content = $url;

        if (!empty($via)) {
            $content.= ' (via @' . $via . ')';
        }

        $remaining_chars = 160 - strlen($content) - 2;

        if ($remaining_chars > 0) {
            $text = str_reduce($text, $remaining_chars - 3, '...');

            if (!empty($url)) {
                $text = $text . ': ';
            }
            $content = $text . $content;
        }

        return self::TWITTER_SHARE_BASE_URL . '?text=' . urlencode($content);
    }

    public static function googleplus_share_url($url)
    {
        // https://plus.google.com/share?url=urrrl
        return self::GOOGLEPLUS_SHARE_BASE_URL . '?url=' . urlencode($url);
    }

}