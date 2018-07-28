<?php

/**
 * Check.class [ HELPER ]
 * Classe responável por manipular e validade dados do sistema!
 *
 * @copyright (c) 2018, Edinei J. Bauer
 */


namespace Core;

class Check
{

    /**
     * <b>Tranforma URL:</b> Tranforma uma string no formato de URL amigável e retorna o a string convertida!
     * @param STRING $Name = Uma string qualquer
     * @return STRING
     */
    public static function name($Name)
    {
        $f = array();
        $f['a'] = 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜüÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿRr|"!@#$%&*()_-+={[}]/?;:.,\\\'<>°ºª¹²³£¢¬™®★’`§☆●•…”“’‘♥♡■◎≈◉';
        $f['b'] = "aaaaaaaceeeeiiiidnoooooouuuuuybsaaaaaaaceeeeiiiidnoooooouuuyybyRr                                                            ";

        $data = strtr(utf8_decode($Name), utf8_decode($f['a']), $f['b']);
        $data = strip_tags(trim($data));
        $data = str_replace(' ', '-', $data);
        $data = str_replace(array('-----', '----', '---', '--'), '-', $data);

        return str_replace('?', '-', utf8_decode(strtolower(utf8_encode($data))));
    }

    /**
     * Codifica senha
     * @param string $senha
     * @return string
     */
    public static function password(string $senha): string
    {
        return md5(str_replace(['1', 'c', 's', '2', 'r', 'o', 'n', 'l', 'f', 'x', '0', 'k', 'v', '5', 'y'], ['b', '4', '9', '6', 'w', 'a', 'd', '3', 'z', '7', 'j', 'm', '8', 'h', 't'], md5("t" . trim($senha) . "0!")));
    }

    /**
     * <b>Limita as Palavras:</b> Limita a quantidade de palavras a serem exibidas em uma string!
     *
     * @param string $string
     * @param int $limite
     * @param string|null $pointer
     * @return string
     */
    public static function words(string $string, int $limite = 20, string $pointer = null): string
    {
        $string = strip_tags(trim($string));

        $arrWords = explode(' ', $string);
        $newWords = implode(' ', array_slice($arrWords, 0, $limite));

        $pointer = (empty($pointer) ? '...' : ' ' . $pointer);
        $result = ($limite < count($arrWords) ? $newWords . $pointer : $string);

        return $result;
    }

    /**
     * Convert imagem recebida em formato json ou array armazenada pelo sistema da singular
     * array ou json format [["url" => "link", "size" => 335]]
     *
     * @param mixed $json
     * @return string
     */
    public static function image($json): string
    {
        if (empty($json))
            return "";

        if (defined("HOME")) {
            if (is_array($json) && !empty($json[0]['url']))
                return HOME . str_replace('\\', '/', $json[0]['url']);
            elseif (Validate::json($json) && preg_match('/url/i', $json))
                return HOME . str_replace('\\', '/', json_decode($json, true)[0]['url']);
            elseif (is_string($json))
                return HOME . $json;
        }
        return "";
    }

    /**
     * Converte cor hexadecimal para RGB ->retorna array('red' => 255, 'green' => 112, 'blue' => 114)
     * @param string $colour
     * @return mixed
     */
    public static function hex2rgb(string $colour)
    {
        if ($colour[0] == '#')
            $colour = substr($colour, 1);

        if (strlen($colour) == 6)
            list($r, $g, $b) = array($colour[0] . $colour[1], $colour[2] . $colour[3], $colour[4] . $colour[5]);
        elseif (strlen($colour) == 3)
            list($r, $g, $b) = array($colour[0] . $colour[0], $colour[1] . $colour[1], $colour[2] . $colour[2]);
        else
            return false;

        $r = hexdec($r);
        $g = hexdec($g);
        $b = hexdec($b);
        return array('red' => $r, 'green' => $g, 'blue' => $b);
    }

    /**
     * Converte os valores no tipo string para os tipos corretos dos valores
     * ex: convert "true" => true; "12" => 12
     *
     * @param string $string
     * @return mixed
     */
    public static function getValue(string $string)
    {
        if ($string === "TRUE" || $string === "true" || $string === "false" || $string === "FALSE")
            return $string === "TRUE" || $string === "true";
        elseif (is_numeric($string) && preg_match('/\./i', $string))
            return (float)$string;
        elseif (is_numeric($string))
            return (int)$string;
        elseif (Validate::json($string))
            return self::getValueList(json_decode($string, true));
        elseif (is_array($string))
            return self::getValueList($string);

        return (string)$string;
    }

    /**
     * Converte os valores no tipo string de um array nos tipos corretos dos valores
     * ex: convert "true" => true; "12" => 12
     *
     * @param array $array
     * @return array
     */
    private static function getValueList(array $array): array
    {
        foreach ($array as $i => $attr)
            $array[$i] = (is_array($attr) ? self::getValueList($attr) : self::getValue($attr));

        return $array;
    }
}
