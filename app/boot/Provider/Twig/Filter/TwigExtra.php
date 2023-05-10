<?php

namespace Boot\Provider\Twig\Filter;


use Twig\TwigFilter;
use Twig\TwigFunction;
use Slim\Views\TwigRuntimeExtension;
use Psr\Container\ContainerInterface;
use Twig\Extension\AbstractExtension;

class TwigExtra extends AbstractExtension
{
    /** @var ContainerInterface */
    public $container;
    public function __construct(ContainerInterface &$container)
    {
        $this->container = $container;
    }

    public function getFilters()
    {
        return [
            new TwigFilter('json_encode', [$this, 'jsonEncode']),
            new TwigFilter('json_decode', [$this, 'jsonDecode']),
            new TwigFilter('serializer', [$this, 'serializer']),
            new TwigFilter('unserializer', [$this, 'unserializer']),
            new TwigFilter('thumb_video', [$this, 'thumbVideo']),
            new TwigFilter('embed_video', [$this, 'embedVideo']),

            new TwigFilter('preg_filter', [$this, '_preg_filter']),
            new TwigFilter('preg_grep', [$this, '_preg_grep']),
            new TwigFilter('preg_match', [$this, '_preg_match']),
            new TwigFilter('preg_quote', [$this, '_preg_quote']),
            new TwigFilter('preg_quote', [$this, '_preg_quote']),
            new TwigFilter('preg_replace', [$this, '_preg_replace']),
            new TwigFilter('preg_split', [$this, '_preg_split']),

            new TwigFilter('share_whatsapp', [$this, '_share_whatsapp']),
            new TwigFilter('share_facebook', [$this, '_share_facebook']),
            new TwigFilter('share_linkedin', [$this, '_share_linkedin']),
            new TwigFilter('share_twitter', [$this, '_share_twitter']),

        ];
    }

    public function getFunctions()
    {
        $options = [
            'needs_context' => true,
            'is_safe'       => ['html']
        ];

        return [
            new TwigFunction('path', [TwigRuntimeExtension::class, 'urlFor']),
            new TwigFunction('url', [TwigRuntimeExtension::class, 'fullUrlFor']),
            new TwigFunction('getContainer', [$this, 'getContainer'], $options),
            new TwigFunction('getGoogle', [$this, 'getGoogle'], $options),
            new TwigFunction('getApi', [$this, 'getApi'], $options),
            new TwigFunction('getApp', [$this, 'getApp'], $options),
            new TwigFunction('getDebug', [$this, 'getDebug'], $options),
        ];
    }


    public function getContainer()
    {
        return $this->container;
    }


    public function getApp()
    {
        return $this->container->get('_app');
    }

    public function getApi()
    {
        return $this->container->get('_api');
    }
    
    public function getGoogle()
    {
        return $this->container->get('_google');
    }
    
    public function getDebug()
    {
        return $this->container->get('debug');
    }

    /**
     * @param array $array
     *
     * @return false|string|null
     */
    public function jsonEncode($array = [])
    {
        return is_array($array) ? @json_encode($array) : null;
    }

    /**
     * @param $string
     *
     * @return mixed|null
     */
    public function jsonDecode($string)
    {
        return !empty($string) ? @json_decode($string, true) : null;
    }

    /**
     * @param array $array
     *
     * @return string|null
     */
    public function serializer($array = [])
    {
        return is_array($array) ? serialize($array) : null;
    }

    /**
     * @param null $string
     *
     * @return array|mixed
     */
    public function unserializer($string = null)
    {
        return !empty($string) ? unserialize($string) : [];
    }

    /**
     * @param $codigo
     * @param $local
     *
     * @return array|string[]|null
     */
    public function thumbVideo($codigo, $local)
    {
        switch ($local) {
            case "youtube":
                return [
                    'd'   => "https://i.ytimg.com/vi/{$codigo}/default.jpg",
                    'mq'  => "https://i.ytimg.com/vi/{$codigo}/mqdefault.jpg",
                    'hq'  => "https://i.ytimg.com/vi/{$codigo}/hqdefault.jpg",
                    'sd'  => "https://i.ytimg.com/vi/{$codigo}/sddefault.jpg",
                    'max' => "https://i.ytimg.com/vi/{$codigo}/maxresdefault.jpg"
                ];
                break;
            case "vimeo":
                try {
                    $vimeo = current(json_decode(file_get_contents("http://vimeo.com/api/v2/video/{$codigo}.json"), true));
                    return [
                        'd'   => $vimeo['thumbnail_small'],
                        'mq'  => $vimeo['thumbnail_medium'],
                        'hq'  => $vimeo['thumbnail_medium'],
                        'sd'  => $vimeo['thumbnail_large'],
                        'max' => $vimeo['thumbnail_large']
                    ];
                } catch (\Exception $e) {
                    return null;
                }

                break;
            default:
                return $codigo;
                break;
        }
    }

    /**
     * @param $codigo
     * @param $local
     *
     * @return string
     */
    public function embedVideo($codigo, $local)
    {
        switch ($local) {
            case "youtube":
                return "https://www.youtube.com/embed/{$codigo}";
                break;
            case "vimeo":
                return "https://player.vimeo.com/video/{$codigo}";
                break;
            default:
                return $codigo;
                break;
        }
    }

    /**
     * @param        $subject
     * @param        $pattern
     * @param string $replacement
     * @param int $limit
     *
     * @return string|string[]|null
     */
    public function _preg_filter($subject, $pattern, $replacement = '', $limit = -1)
    {
        return isset($subject) ? preg_filter($pattern, $replacement, $subject, $limit) : null;
    }

    /**
     * @param $subject
     * @param $pattern
     *
     * @return array|null
     */
    public function _preg_grep($subject, $pattern)
    {
        return isset($subject) ? preg_grep($pattern, $subject) : null;
    }


    /**
     * @param $subject
     * @param $pattern
     *
     * @return false|int|null
     */
    public function _preg_match($subject, $pattern)
    {
        return isset($subject) ? preg_match($pattern, $subject) : null;
    }


    /**
     * @param $subject
     * @param $delimiter
     *
     * @return string|null
     */
    public function _preg_quote($subject, $delimiter)
    {
        return isset($subject) ? preg_quote($subject, $delimiter) : null;
    }


    /**
     * @param        $subject
     * @param        $pattern
     * @param string $replacement
     * @param int $limit
     *
     * @return string|string[]|null
     */
    public function _preg_replace($subject, $pattern, $replacement = '', $limit = -1)
    {
        return isset($subject) ? preg_replace($pattern, $replacement, $subject, $limit) : null;
    }


    /**
     * @param $subject
     * @param $pattern
     *
     * @return array|false|string[]|null
     */
    public function _preg_split($subject, $pattern)
    {
        return isset($subject) ? preg_split($pattern, $subject) : null;
    }

    /**
     * @param null $texto
     * @param null $numero
     * @param array $add
     *
     * @return string
     */
    public function _share_whatsapp($texto = null, $numero = null, $add = [])
    {
        $baseUrl = 'https://api.whatsapp.com/send';
        $dataUrl = [];
        if (!empty($numero)) {
            $dataUrl['phone'] = '55' . str_replace(['(', ')', '-', " "], '', $numero);
        }
        if (!empty($texto)) {
            $dataUrl['text'] = $texto;
        }

        if (empty($dataUrl['phone']) && empty($dataUrl['text'])) {
            return "";
        }

        if (!empty($add)) {
            if (!empty($add['baseurl'])) {
                $baseUrl = $add['baseurl'];
                unset($add['baseurl']);
            }
            $dataUrl = array_merge($dataUrl, $add);
        }

        return sprintf("%s?%s", $baseUrl, http_build_query($dataUrl));
    }

    /**
     * @param null $url
     * @param array $add
     *
     * @return string
     */
    public function _share_facebook($url = null, $add = [])
    {
        $baseUrl = 'https://www.facebook.com/sharer/sharer.php';
        $dataUrl = [
            'u' => $url
        ];
        if (!empty($add)) {
            if (!empty($add['baseurl'])) {
                $baseUrl = $add['baseurl'];
                unset($add['baseurl']);
            }
            $dataUrl = array_merge($dataUrl, $add);
        }

        return sprintf("%s?%s", $baseUrl, http_build_query($dataUrl));
    }

    /**
     * @param null $url
     * @param null $titulo
     * @param array $add
     *
     * @return string
     */
    public function _share_linkedin($url = null, $titulo = null, $add = [])
    {
        $baseUrl = 'https://www.linkedin.com/shareArticle';
        $dataUrl = [
            'mini' => 'true',
            'url'  => $url
        ];
        if (!empty($titulo)) {
            $dataUrl['title'] = $titulo;
        }

        if (!empty($add)) {
            if (!empty($add['baseurl'])) {
                $baseUrl = $add['baseurl'];
                unset($add['baseurl']);
            }
            $dataUrl = array_merge($dataUrl, $add);
        }

        return sprintf("%s?%s", $baseUrl, http_build_query($dataUrl));
    }

    /**
     * @param null $status
     * @param array $add
     *
     * @return string
     */
    public function _share_twitter($status = null, $add = [])
    {
        $baseUrl = 'https://twitter.com/home';
        $dataUrl = [
            'status' => $status
        ];
        if (!empty($add)) {
            if (!empty($add['baseurl'])) {
                $baseUrl = $add['baseurl'];
                unset($add['baseurl']);
            }
            $dataUrl = array_merge($dataUrl, $add);
        }

        return sprintf("%s?%s", $baseUrl, http_build_query($dataUrl));
    }
}
