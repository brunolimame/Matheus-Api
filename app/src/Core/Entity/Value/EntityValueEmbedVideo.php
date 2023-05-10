<?php

namespace Core\Entity\Value;


class EntityValueEmbedVideo
{
    public $embed;
    public $embedAuto;

    public function __construct($codigo, $local)
    {
        $this->loadEmbed($codigo, $local);

    }

    public static function factory($codigo, $local)
    {
        return new self($codigo, $local);
    }

    protected function loadEmbed($codigo, $local):void
    {
        $urlEmbedYoutube  = "https://www.youtube.com/embed/";
        $urlEmbedVimeo    = "https://player.vimeo.com/video/";
        $urlEmbedFacebook = "https://www.facebook.com/plugins/video.php?href=%s&show_text=false&width=734&height=411&appId";
        $embed            = [
            'youtube'  => "{$urlEmbedYoutube}{$codigo}",
            'vimeo'    => "{$urlEmbedVimeo}{$codigo}",
            'facebook' => sprintf($urlEmbedFacebook, $codigo),
        ];

        $embedAutoPlay = [
            'youtube'  => "{$urlEmbedYoutube}{$codigo}?autoplay=1;mute=1",
            'vimeo'    => "{$urlEmbedVimeo}{$codigo}?autoplay=1&muted=1",
            'facebook' => sprintf($urlEmbedFacebook, $codigo) . '&autoplay=1',
        ];

        $this->embed     = !empty($embed[$local]) ? $embed[$local] : null;
        $this->embedAuto = !empty($embedAutoPlay[$local]) ? $embedAutoPlay[$local] : null;
    }

    public function toArray()
    {
        return array_reduce(array_keys(get_object_vars($this)), function ($result, $item) {
            $result[$item] = $this->$item;
            return $result;
        }, []);
    }

    public function __toString()
    {
        return empty($this->embed) ? "" : $this->embed;
    }
}