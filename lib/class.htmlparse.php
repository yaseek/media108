<?php
namespace Yaseek;

class HTMLParse {

    const HTTP_EQUIV = 'http-equiv';
    const CONTENT_TYPE = 'content-type';
    const CONTENT = 'content';

    private $text;
    private $dom;

    public function __construct ($url) {

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_ENCODING, '');

        $this->text = curl_exec($ch);

        if ($this->text) {
            $this->dom = new \DOMDocument();
            libxml_use_internal_errors(TRUE);
            $this->dom->loadHTML($this->text);
            libxml_clear_errors();
            libxml_use_internal_errors(FALSE);
        }
    }

    public function getText () {
        return $this->text;
    }

    public function getCharset () {
        $list = $this->dom->getElementsByTagName('meta');
        $result = NULL;

        foreach ($list as $item) {
            
            $attrs = array();
            foreach ($item->attributes as $attr) {
                $attrs[strtolower($attr->name)] = $attr->value;
            }

            if (array_key_exists(self::HTTP_EQUIV, $attrs) &&
                strtolower($attrs[self::HTTP_EQUIV]) === self::CONTENT_TYPE) {
                
                $content = (array_key_exists(self::CONTENT, $attrs)) 
                        ? $attrs[self::CONTENT] : '';

                if (preg_match('~charset=([^;]+)~i', $content, $matches)) {
                    $result = strtolower(trim($matches[1]));    
                    break;
                }
            }

        }

        return $result;
    }

}