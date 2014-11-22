<?php
namespace Yaseek;

class HTMLParse {

    const HTTP_EQUIV = 'http-equiv';
    const CONTENT_TYPE = 'content-type';
    const CONTENT = 'content';
    const CHARSET = 'charset';

    const ERROR_PAGE = '/_probably_undefined_page';

    private $text;
    private $dom;

    private function request ($url) {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_ENCODING, '');
 
        $text = curl_exec($ch);

        $this->info = $info = curl_getinfo($ch);

        $code = $info['http_code'];
        if ($code >= 300 && $code < 400) {

            return $this->request($info['redirect_url']);

        } elseif ($code < 400 && $text) {
            $this->dom = new \DOMDocument();
            libxml_use_internal_errors(TRUE);
            $this->dom->loadHTML($text);
            libxml_clear_errors();
            libxml_use_internal_errors(FALSE);
            
            return $text;            
        }

    }

    private function extractCharset ($content) {
        if (preg_match('~charset=([^;]+)~i', $content, $matches)) {
            return strtolower(trim($matches[1]));    
        }
    }

    public function __construct ($url) {
        $this->url = $url;
        $this->text = $this->request($url);
    }

    public function getText () {
        return $this->text;
    }

    public function getCharset () {
        $result = NULL;

        if (array_key_exists('content_type', $this->info)) {
            $result = $this->extractCharset($this->info['content_type']);    
        }

        if (!$result && $this->dom) {
            $list = $this->dom->getElementsByTagName('meta');

            foreach ($list as $item) {
                
                // collectattributes on each meta tag
                $attrs = array();
                foreach ($item->attributes as $attr) {
                    $attrs[strtolower($attr->name)] = $attr->value;
                }

                if (array_key_exists(self::CHARSET, $attrs)) {

                    $result = $attrs[self::CHARSET];

                } elseif (array_key_exists(self::HTTP_EQUIV, $attrs) &&
                    strtolower($attrs[self::HTTP_EQUIV]) === self::CONTENT_TYPE) {

                    $content = (array_key_exists(self::CONTENT, $attrs)) 
                            ? $attrs[self::CONTENT] : '';
                    $result = $this->extractCharset($content);
                    if (boolval($result)) {
                        break;
                    }
                }
            }
        }

        return $result;
    }

}