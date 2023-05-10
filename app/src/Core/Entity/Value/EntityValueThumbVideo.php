<?php

namespace Core\Entity\Value;


class EntityValueThumbVideo
{
    public $capa;
    public $d;
    public $mq;
    public $hq;
    public $sd;
    public $max;

    public function __construct($codigo, $local, $capa = null)
    {
        if (!empty($capa) || $local == 'facebook') {
            $this->setCapa($capa);
        } else {
            switch ($local) {
                case "youtube":
                    $this->setYoutube($codigo);
                    break;
                case "vimeo":
                    $this->setVimeo($codigo);
                    break;
            }
        }

    }

    public static function factory($codigo, $local, $capa = null)
    {
        return new self($codigo, $local, $capa);
    }

    protected function setCapa($capa):void
    {
        $this->d    = $capa['thumb'];
        $this->mq   = &$this->d;
        $this->capa = $capa['media'];
        $this->hq   = &$this->capa;
        $this->sd   = &$this->capa;
        $this->max  = $capa[''];
    }

    protected function setYoutube($codigo):void
    {
        $urlThumbYoutube = "https://i.ytimg.com/vi/{$codigo}/";
        $this->d         = $urlThumbYoutube . 'default.jpg';
        $this->mq        = $urlThumbYoutube . 'mqdefault.jpg';
        $this->hq        = $urlThumbYoutube . 'hqdefault.jpg';
        $this->capa      = &$this->hq;
        $this->sd        = $urlThumbYoutube . 'sddefault.jpg';
        $this->max       = $urlThumbYoutube . 'maxresdefault.jp';
    }

    protected function setVimeo($codigo):void
    {
        $vimeo      = @current(@json_decode(@file_get_contents("https://vimeo.com/api/v2/video/{$codigo}.json"), true));
        $this->d    = $vimeo['thumbnail_small'];
        $this->mq   = $vimeo['thumbnail_medium'];
        $this->hq   = $vimeo['thumbnail_medium'];
        $this->capa = &$this->hq;
        $this->sd   = $vimeo['thumbnail_large'];
        $this->max  = $vimeo['thumbnail_large'];
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
        return empty($this->capa) ? "" : $this->capa;
    }
}