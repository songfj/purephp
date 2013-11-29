<?php

class pure_str {

    /**
     * Generates a random string
     * @param int $length
     * @param string $charset 'alpha', 'alphanum', 'alpha_ci', 'alphanum_ci', 'num', 'symbol', 'hex', 'any', or a string with custom set of chars
     * @return string
     */
    public static function random($length = 32, $charset = 'alphanum') {
        if ($charset == 'alpha')
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        else if ($charset == 'alphanum')
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        if ($charset == 'alpha_ci')
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        else if ($charset == 'alphanum_ci')
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        else if ($charset == 'num')
            $chars = '0123456789';
        else if ($charset == 'symbol')
            $chars = '{}()[]<>!?|@#%&/=^*;,:.-_+';
        else if ($charset == 'hex')
            $chars = 'ABCDEF0123456789';
        else if ($charset == 'any')
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789{}()[]<>!?|@#%&/=^*;,:.-_+';
        else
            $chars = $charset;

        $plength = mb_strlen($chars);
        mt_srand((double) microtime() * 10000000);
        $str = '';
        for ($i = 0; $i < $length; $i++) {
            $str .= $chars[mt_rand(0, $plength - 1)];
        }
        return $str;
    }

    /**
     * 
     * @param string $salt
     * @param bool $appendMicrotime
     * @param bool $appendPID
     * @param bool $appendServerAddr
     * @return string Hexadecimal string (variable length)
     */
    public static function uniqid($salt = '', $appendMicrotime = true, $appendPID = true, $appendServerAddr = true) {
        $uuid = uniqid(mt_rand() . $salt, true);
        if ($appendMicrotime === true) {
            usleep(5);
            $uuid.='.' . microtime(true);
        }
        if (($appendPID === true) and (function_exists('getmypid'))) {
            $uuid.='.' . getmypid();
        }
        if (($appendServerAddr === true) and isset($_SERVER['SERVER_ADDR']) and !empty($_SERVER['SERVER_ADDR'])) {
            $uuid.='.' . str_replace(array(':', '.'), '', $_SERVER['SERVER_ADDR']);
            if (isset($_SERVER['SERVER_PORT']) and !empty($_SERVER['SERVER_PORT'])) {
                $uuid.= $_SERVER['SERVER_PORT'];
            }
        }
        return str_replace(array('_', '.', ',', ' ', ':'), '-', $uuid);
    }

    public static function html2nl($html, $htmlTags = array('<br>', '<br/>', '<br />', '<hr>', '<hr/>', '<hr />', '</div>', '</p>', '</tr>')) {
        return strip_tags(strip_tags(str_replace($htmlTags, "\n", $html)));
    }

    public static function toHex($str) {
        $hex = '';
        for ($i = 0; $i < strlen($str); $i++) {
            $ord = ord($str[$i]);
            $hexCode = dechex($ord);
            $hex .= substr('0' . $hexCode, -2);
        }
        return strToUpper($hex);
    }

    public static function fromHex($hex) {
        $str = '';
        for ($i = 0; $i < strlen($hex) - 1; $i+=2) {
            $str .= chr(hexdec($hex[$i] . $hex[$i + 1]));
        }
        return $str;
    }

    /**
     * Returns a camelized string.
     * @param string $str
     * @param boolean $lcfirst
     * @return string
     */
    public static function camelize($str, $lcfirst = false) {
        $str = str_replace(' ', '', ucwords(self::slugize($str, ' ')));
        if ($lcfirst) {
            $str = lcfirst($str);
        }
        return $str;
    }

    /**
     * Humanizes a camelized string, separating words (if not using a word separator)
     * 
     * @param string $str Camelized string
     * @param string $glue Delimiter that will be used for separating words
     * @return string 
     */
    public static function uncamelize($str, $glue = '_') {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $str, $matches);
        return implode($glue, (isset($matches[0]) and is_array($matches[0])) ? $matches[0] : array());
    }

    /**
     * Converts any string to a friendly-url string.
     * Uses transliteration for special chars.
     * @param string $str
     * @param string $delimiter
     * @param array $replace Extra characters to be replaced with delimiter
     * @return string
     */
    public static function slugize($str, $delimiter = '-', $replace = array()) {
        if (!empty($replace)) {
            $str = str_replace((array) $replace, ' ', $str);
        }
        return preg_replace('/[\/_|+ -]+/', $delimiter, strtolower(
                        trim(preg_replace('/[^a-zA-Z0-9\/_|+ -]/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $str)), '- ')));
    }

    /**
     * Converts a friendly url-like formatted string to a human readable string.
     * Detects '-' and '_' word separators by default.
     * @param string $str
     * @param string $delimiter
     * @return string
     */
    public static function unslugize($str, $delimiter = '-') {
        return str_replace($delimiter, ' ', $str);
    }

    /**
     * Cleans a string with the specified rules
     * @param string $str
     * @param array $remove options
     * 
     * @return string 
     */
    public static function clean($str, $remove = array('html' => true, 'quotes' => true, 'backslashes' => true, 'trim' => true)) {
        $remove = array_merge(array('html' => true, 'quotes' => true, 'backslashes' => true, 'trim' => true), $remove);

        if ($remove['html']) { // remove html
            $str = strip_tags($str);
        }
        if ($remove['quotes']) { // replace quotes and diacritic accents
            $str = str_replace(array("'", '"', '´', '`'), '', $str);
        }
        if ($remove['backslashes']) { // remove backslashes
            $str = str_replace(array('\\'), '', $str);
        }
        if ($remove['trim']) { // remove trailing whitespace and newlines
            $str = trim($str);
        }
        return $str;
    }

    /**
     * Emulates mysql_escape_string, but without collation handling and doesn't
     * need an active mysql connection.
     * 
     * @param type $str
     * @return string 
     */
    public static function escape($str) {
        $search = array("\\", "\x00", "\n", "\r", "\t", "\b", "'", '"', "\x1a");
        $replace = array("\\\\", "\\0", "\\n", "\\r", "\\t", "\\b", "\'", '\"', "\\Z");

        return str_replace($search, $replace, $str);
    }

    /**
     * Concatenates one or more strings, but only if they are not empty
     * 
     * @param string $glue
     * @param array|string $pieces An array of pieces or you can
     * pass multiple arguments as strings ($piece1, $piece2, ...)
     */
    public static function concat($glue, $pieces) {
        $args = func_get_args();
        $glue = array_shift($args);

        if ((count($args) == 1) && is_array($args[0])) {
            $pieces = $args[0];
        } else {
            $pieces = $args;
        }

        $pieces2 = array();
        foreach ($pieces as $i => $p) {
            if (!empty($p)) {
                $pieces2[] = $p;
            }
        }

        return implode($glue, $pieces2);
    }

    /**
     * Cuts a string if it exceeds the given $length, and appends the $append param
     * @param string $str Original string
     * @param int $length Max length
     * @param string $append String that will be appended if the original string exceeds $length
     * @return string 
     */
    public static function truncate($str, $length, $append = '') {
        if (($length > 0) && (strlen($str) > $length)) {
            return substr($str, 0, $length) . $append;
        } else {
            return $str;
        }
    }

    /**
     * Cuts a string by entire words if it exceeds the given $length, and appends the $append param
     * @param string $str Original string
     * @param int $length Max length
     * @param string $append String that will be appended if the original string exceeds $length
     * @return string 
     */
    public static function truncateWords($str, $length, $append = '') {
        $str2 = self::replaceRepeated($str, '\\s', ' ');
        $words = explode(' ', $str2);
        if (($length > 0) && (count($words) > $length)) {
            return implode(' ', array_slice($words, 0, $length)) . $append;
            //return substr($str, 0, $length).$append;
        }
        else
            return $str;
    }

    /**
     * Replaces repeated characters
     * 
     * @param string $str
     * @param string $char
     * @param string $replacement
     * @return string
     */
    public static function replaceRepeated($str, $char, $replacement = '') {
        return preg_replace('/' . $char . $char . '+/', $replacement, $str);
    }

    public static function isHtml($str) {
        return (preg_match('/<\/?\w+((\s+\w+(\s*=\s*(?:".*?"|\'.*?\'|[^\'">\s]+))?)+\s*|\s*)\/?>/i', $str) > 0);
    }

    public static function isMsWord($str) {
        return preg_match('/class="?Mso|style="[^"]*\bmso-|w:WordDocument/i', $str) != false;
    }

    public static function isUrl($str) {
        // Supports protocol agnostic urls starting with double dash '//'
        return (filter_var($str, FILTER_VALIDATE_URL) !== false) || (preg_match('/^\/\/.+/', $str) == true);
    }

    public static function isEmail($str) {
        return filter_var($str, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function isWebFile($str, $exts = null) {
        $exts = $exts ? $exts : 'js|j|css|less|xml|xss|xslt|rss|atom|json|cur|ani|ico|bmp|jpg|png|apng|gif|swf|' .
                'svg|svgz|otf|eot|woff|ttf|avi|fla|flv|mp3|mp4|mpg|mov|mpeg|mkv|ogg|ogv|oga|aac|wmv|wma|rm|webm|webp|pdf';
        return (preg_match("/\.({$exts})$/", $str) != false);
    }

    public static function isRegex($str) {
        return (preg_match('/^\/.*\/[imsxeADSUXJu]*$/', $str)) > 0;
    }

    public static function isJson($str) {
        return is_object(@json_decode($str, false, 1));
    }

    /**
     * Encrypts an string using MCRYPT_RIJNDAEL_256 and MCRYPT_MODE_ECB
     * 
     * @param string $text The RAW string
     * @param string $key The key with which the data will be encrypted.
     * @return string|false The encrypted and base64-safe-encoded string (safe for urls)
     */
    public static function encrypt($text, $key) {
        if (empty($text)) {
            return false;
        }

        return self::base64encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($key), $text, MCRYPT_MODE_CBC, md5(md5($key))), true);
    }

    /**
     * Decrypts an string previously encrypted using crypto_encrypt
     * 
     * @param string $encrypted The RAW encrypted string
     * @param string $salt The key with which the data was encrypted.
     * @return string|false The decrypted string 
     */
    public static function decrypt($encrypted, $key) {
        if (empty($encrypted)) {
            return false;
        }
        return rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key), self::base64decode($encrypted, true), MCRYPT_MODE_CBC, md5(md5($key))), '\0');
    }

    /**
     * Generate a keyed hash value using the HMAC method
     * @link http://php.net/manual/en/function.hash-hmac.php
     * @param string $algo <p>
     * Name of selected hashing algorithm (i.e. 'md5', 'sha256', 'haval160,4', etc..) See <b>hash_algos</b> for a list of supported algorithms.
     * </p>
     * @param string $data <p>
     * Message to be hashed.
     * </p>
     * @param string $key <p>
     * Shared secret key used for generating the HMAC variant of the message digest.
     * </p>
     * @param bool $raw_output [optional] <p>
     * When set to true, outputs raw binary data.
     * false outputs lowercase hexits.
     * </p>
     * @return string a string containing the calculated message digest as lowercase hexits
     * unless <i>raw_output</i> is set to true in which case the raw
     * binary representation of the message digest is returned.
     */
    public static function hmac($algo, $data, $key, $raw_output = false) {
        if (function_exists('hash_hmac')) {
            return hash_hmac($algo, $data, $key, $raw_output);
        } else {
            $blocksize = 64;
            if (strlen($key) > $blocksize)
                $key = pack('H*', $algo($key));

            $key = str_pad($key, $blocksize, chr(0x00));
            $ipad = str_repeat(chr(0x36), $blocksize);
            $opad = str_repeat(chr(0x5c), $blocksize);
            $hmac = pack('H*', $algo(($key ^ $opad) . pack('H*', $algo(($key ^ $ipad) . $data))));

            return $raw_output ? $hmac : bin2hex($hmac);
        }
    }

    /**
     * Generate a hash value using the sha256 algorithm
     * 
     * @param string $data Message to be hashed.
     * @param boolean $raw_output
     * @return string 
     */
    public static function sha256($data, $raw_output = false) {
        return hash('sha256', $data, $raw_output);
    }

    /**
     * Generate a hash value using the sha512 algorithm
     * 
     * @param type $data Message to be hashed.
     * @param boolean $raw_output
     * @return string 
     */
    public static function sha512($data, $raw_output = false) {
        return hash('sha512', $data, $raw_output);
    }

    /**
     * Base64 encode (Binary to ASCII or btoa in javascript)
     * @param string $data
     * @param bool $url_safe
     * @return string The base64 ASCII string
     */
    public static function base64encode($data, $url_safe = false) {
        $data = base64_encode($data);
        if ($url_safe) {
            $data = str_replace(array('+', '/', '='), array('-', '_', ''), $data);
        }
        return $data;
    }

    /**
     * Base64 decode (ASCII to binary or atob in javascript)
     * @param string $data String encoded using str::base64encode()
     * @param bool $url_safe
     * @return string The binary string
     */
    public static function base64decode($data, $url_safe = false) {
        if ($url_safe) {
            $data = str_replace(array('-', '_'), array('+', '/'), $data);
            $mod4 = strlen($data) % 4;
            if ($mod4) {
                $data .= substr('====', $mod4);
            }
        }
        return base64_decode($data);
    }

    /**
     * (PHP 4 &gt;= 4.0.1, PHP 5)<br/>
     * Compress a string using the ZLIB format
     * @link http://php.net/manual/en/function.gzcompress.php
     * @param string $data <p>
     * The string to compress.
     * </p>
     * @param int $level [optional] <p>
     * The level of compression. Can be given as 0 for no compression up to 9
     * for maximum compression.
     * </p>
     * <p>
     * If -1 is used, the default compression of the zlib library is used which is 6.
     * </p>
     * @return string The compressed string as binary or <b>FALSE</b> if an error occurred.
     */
    public static function compress($data, $level = 9) {
        return gzcompress($data, $level);
    }

    /**
     * (PHP 4 &gt;= 4.0.1, PHP 5)<br/>
     * Uncompress a compressed string using the ZLIB format
     * @link http://php.net/manual/en/function.gzuncompress.php
     * @param string $data <p>
     * The binary string compressed by <b>gzcompress</b>.
     * </p>
     * @param int $maxLength [optional] <p>
     * The maximum length of data to decode.
     * </p>
     * @return string The original uncompressed string or <b>FALSE</b> on error.
     * </p>
     * <p>
     * The function will return an error if the uncompressed string is more than
     * 32768 times the length of the compressed input <i>string</i>
     * or more than the optional parameter <i>maxLength</i>.
     */
    public static function uncompress($data, $maxLength = 0) {
        return gzuncompress($data, $maxLength);
    }

    public static function parseIniString($str) {

        if (empty($str)) {
            return array();
        }

        $lines = explode("\n", $str);
        $ret = array();
        $inside_section = false;

        foreach ($lines as $line) {

            $line = trim($line);

            if (!$line || $line[0] == "#" || $line[0] == ";")
                continue;

            if ($line[0] == "[" && $endIdx = strpos($line, "]")) {
                $inside_section = substr($line, 1, $endIdx - 1);
                continue;
            }

            if (!strpos($line, '='))
                continue;

            $tmp = explode("=", $line, 2);

            if ($inside_section) {

                $key = rtrim($tmp[0]);
                $value = ltrim($tmp[1]);

                if (preg_match("/^\".*\"$/", $value) || preg_match("/^'.*'$/", $value)) {
                    $value = mb_substr($value, 1, mb_strlen($value) - 2);
                }

                $t = preg_match("^\[(.*?)\]^", $key, $matches);
                if (!empty($matches) && isset($matches[0])) {

                    $arr_name = preg_replace('#\[(.*?)\]#is', '', $key);

                    if (!isset($ret[$inside_section][$arr_name]) || !is_array($ret[$inside_section][$arr_name])) {
                        $ret[$inside_section][$arr_name] = array();
                    }

                    if (isset($matches[1]) && !empty($matches[1])) {
                        $ret[$inside_section][$arr_name][$matches[1]] = $value;
                    } else {
                        $ret[$inside_section][$arr_name][] = $value;
                    }
                } else {
                    $ret[$inside_section][trim($tmp[0])] = $value;
                }
            } else {

                $ret[trim($tmp[0])] = ltrim($tmp[1]);
            }
        }
        return $ret;
    }

}