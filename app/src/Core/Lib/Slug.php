<?php

namespace Core\Lib;

use Cocur\Slugify\Slugify;

abstract class Slug
{
    /**
     * @param $str
     *
     * @return mixed
     */
    static public function slug($str)
    {
        if (empty($str)) {
            return "";
        }
        $slugify = new Slugify();
        $slugify->activateRuleset('esperanto');

        return $slugify->slugify($str);
    }

    /**
     * @param $tipo
     * @param $url
     * @return mixed
     */
    static public function limparVideo($tipo, $url)
    {
        switch ($tipo) {
            case "youtube":
                return self::limparYoutube($url);
                break;
            case "vimeo":
                return self::limparVimeo($url);
                break;
            default:
                return $url;
                break;
        }
    }

    /**
     * @param $url
     * @return mixed
     */
    static public function limparYoutube($url)
    {
        $shortUrlRegex = '/youtu.be\/([a-zA-Z0-9_-]+)\??/i';
        $longUrlRegex  = '/youtube.com\/((?:embed)|(?:watch))((?:\?v\=)|(?:\/))([a-zA-Z0-9_-]+)/i';

        if (preg_match($longUrlRegex, $url, $matches)) {
            $youtube_id = $matches[count($matches) - 1];
        }

        if (preg_match($shortUrlRegex, $url, $matches)) {
            $youtube_id = $matches[count($matches) - 1];
        }

        return empty($youtube_id) ? $url : $youtube_id;
    }

    /**
     * @param $url
     * @return mixed
     */
    static public function limparVimeo($url)
    {
        $urlRegex = '/(?:www\.|player\.)?vimeo.com\/(?:channels\/(?:\w+\/)?|groups\/(?:[^\/]*)\/videos\/|album\/(?:\d+)\/video\/|video\/|)(\d+)(?:[a-zA-Z0-9_\-]+)?/i';
        if (preg_match($urlRegex, $url, $matches)) {
            return $matches[1];
        }
        return $url;
    }
}