<?php

/**
 * Classe responável por ajudar em atividades cumuns de desenvolvimento
 *
 * @copyright (c) 2018, Edinei J. Bauer
 */

namespace Core;

class Helper
{

    /**
     * retorna cor auxiliar inversa em hexadecimal
     * @param string $name = hexadecimal color
     * @return string
     */
    public static function corAuxiliar(string $name): string
    {
        $style = str_replace("#", "", $name);
        $style = strlen($style) === 3 ? $style[0] . $style[0] . $style[1] . $style[1] . $style[2] . $style[2] : $style;

        $todo = hexdec($style[0] . $style[1]) + hexdec($style[2] . $style[3]) + hexdec($style[4] . $style[5]) - 550;
        $base = $todo > 0 ? round($todo / 60) : "F";

        return "#{$base}{$base}{$base}";
    }

    /**
     * <b>listFolder:</b> Lista os arquivos e pastas de uma pasta.
     * @param string $dir = nome do diretório a ser varrido
     * @param int $limit = nome do diretório a ser varrido
     * @return array $directory = lista com cada arquivo e pasta no diretório
     */
    public static function listFolder(string $dir, int $limit = 5000): array
    {
        $directory = [];
        if (file_exists($dir)) {
            $i = 0;
            foreach (scandir($dir) as $b):
                if ($b !== "." && $b !== ".." && $i < $limit):
                    $directory[] = $b;
                    $i++;
                endif;
            endforeach;
        }

        return $directory;
    }

    /**
     * @param array $array1
     * @param array $array2
     * @return array
     */
    public static function array_merge_recursive_distinct(array &$array1, array &$array2): array
    {
        $merged = $array1;
        foreach ($array2 as $key => &$value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key]))
                $merged[$key] = self::array_merge_recursive_distinct($merged[$key], $value);
            else
                $merged[$key] = $value;
        }
        return $merged;
    }

    /**
     * <b>Obtem IP real:</b> obtem o IP real do usuário que esta acessando
     * @return STRING = IP de origem do acesso
     */
    public static function getIP()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])):
            return $_SERVER['HTTP_CLIENT_IP'];
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])):
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        endif;

        return $_SERVER['REMOTE_ADDR'];
    }

    /**
     * Envia um POST REQUEST a uma url com data
     * obtem a resposta
     *
     * @param string $url
     * @param array $data
     * @return bool|string
     */
    public static function postRequest(string $url, array $data = [])
    {
        $url = str_replace("&amp;", "&", urldecode(trim($url)));

        if (Validate::online($url)) {
            $options = array('http' => array('header' => "Content-type: application/x-www-form-urlencoded\r\n", 'method' => 'POST', 'content' => http_build_query($data)));
            $context = stream_context_create($options);

            return file_get_contents($url, false, $context);
        }

        return false;
    }

    public static function createFolderIfNoExist($folder)
    {
        if (!file_exists($folder) && !is_dir($folder))
            mkdir($folder, 0644);
    }
}
