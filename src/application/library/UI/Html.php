<?php

namespace UI;

class Html {

    /**
     * @see https://w3c.github.io/webappsec-subresource-integrity/#the-integrity-attribute
     */
    private static function hash($url, $hash_algo = 'sha384') {
        $data = file_get_contents($url);
        $base64_value = base64_encode(hash($hash_algo, $data, true));
        return $hash_algo.'-'.$base64_value;
    }

    public static function importScript($url) {
        echo '<script src="'.$url.'" integrity="'.self::hash($url).'" crossorigin="anonymous"></script>'.PHP_EOL;
    }

    public static function importCss($url) {
        echo '<link rel="stylesheet" href="'.$url.'" integrity="'.self::hash($url).'" crossorigin="anonymous">'.PHP_EOL;
    }

}