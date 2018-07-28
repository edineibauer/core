<?php

/**
 * Valida dados
 *
 * @copyright (c) 2018, Edinei J. Bauer
 */

namespace Core;

class Validate
{

    /**
     * <b>Verifica E-mail:</b> Executa validação de formato de e-mail. Se for um email válido retorna true, ou retorna false.
     * @param STRING $email = Uma conta de e-mail
     * @return BOOL = True para um email válido, ou false
     */
    public static function email($email)
    {
        return preg_match('/[a-z0-9_\.\-]+@[a-z0-9_\.\-]*[a-z0-9_\.\-]+\.[a-z]{2,4}$/', $email);
    }

    public static function ajax()
    {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
            return true;

        return false;
    }

    public static function json($string)
    {
        if(is_string($string)) {
            json_decode($string);
            return (json_last_error() == JSON_ERROR_NONE);
        } else {
            return false;
        }
    }

    /**
     * Valida CNPJ
     *
     * @param string $cnpj
     * @return bool
     */
    public static function cnpj(string $cnpj): bool
    {
        $cnpj = preg_replace('/[^0-9]/', '', (string)$cnpj);

        // Valida tamanho
        if (strlen($cnpj) != 14)
            return false;

        // Valida primeiro dígito verificador
        for ($i = 0, $j = 5, $soma = 0; $i < 12; $i++) {
            $soma += $cnpj{$i} * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }
        $resto = $soma % 11;
        if ($cnpj{12} != ($resto < 2 ? 0 : 11 - $resto))
            return false;

        // Valida segundo dígito verificador
        for ($i = 0, $j = 6, $soma = 0; $i < 13; $i++) {
            $soma += $cnpj{$i} * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }
        $resto = $soma % 11;
        return $cnpj{13} == ($resto < 2 ? 0 : 11 - $resto);
    }

    /**
     * Valida CPF
     *
     * @param string $cpf
     * @return bool
     */
    public static function cpf(string $cpf): bool
    {
        $cpf = preg_replace('/[^0-9]/', '', (string)$cpf);
        if (strlen($cpf) !== 11 || $cpf === '00000000000' || $cpf === '11111111111' || $cpf === '22222222222' || $cpf === '33333333333' || $cpf === '44444444444' || $cpf === '55555555555' || $cpf === '66666666666' || $cpf === '77777777777' || $cpf === '88888888888' || $cpf === '99999999999')
            return false;

        for ($i = 0, $j = 10, $soma = 0; $i < 9; $i++, $j--)
            $soma += $cpf{$i} * $j;

        $resto = $soma % 11;
        if ($cpf{9} != ($resto < 2 ? 0 : 11 - $resto))
            return false;

        for ($i = 0, $j = 11, $soma = 0; $i < 10; $i++, $j--)
            $soma += $cpf{$i} * $j;

        $resto = $soma % 11;
        return $cpf{10} == ($resto < 2 ? 0 : 11 - $resto);

    }

    /**
     * <b>Verifica se o endereço passado é de uma imagem:</b>
     * @param string $url
     * @return bool
     */
    public static function image(string $url): bool
    {
        $javascript_loop = 0;
        $timeout = 15;
        $url = str_replace("&amp;", "&", urldecode(trim($url)));

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; WOW64; rv:52.0) Gecko/20100101 Firefox/52.0");
        curl_setopt($ch, CURLOPT_REFERER, $url);

        curl_setopt($ch, CURLOPT_COOKIEJAR, tempnam("/tmp", "CURLCOOKIE"));
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_ENCODING, "gzip, deflate, br");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);    # required for https urls
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);

        $content = curl_exec($ch);
        $response = curl_getinfo($ch);
        curl_close($ch);

        if ($response['http_code'] == 301 || $response['http_code'] == 302)
            return false;
        elseif ($response['http_code'] !== 200 || ((preg_match("/>[[:space:]]+window\.location\.replace\('(.*)'\)/i", $content, $value) || preg_match("/>[[:space:]]+window\.location\=\"(.*)\"/i", $content, $value)) && $javascript_loop < 5))
            return false;
        elseif (!preg_match('/image/i', $response['content_type']) || empty($content) || $content == '')
            return false;

        return true;
    }

    /**
     * <b>Online:</b> Verifica se a url passada esta online e funcionando
     * @param STRING $url = Url a ser verifica o status online
     * @return BOOLEAN
     */
    public static function online($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code === 200):
            return true;
        endif;

        return false;
    }
}
