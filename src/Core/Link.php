<?php

/**
 * Responável por gerenciar e fornecer informações sobre o link url!
 *
 * @copyright (c) 2018, Edinei J. Bauer
 */

namespace Core;

use MatthiasMullie\Minify;

class Link
{
    private $url;
    private $param;
    private $dicionario;
    private $devLibrary;

    /**
     * Link constructor.
     * @param string $lib
     * @param string $file
     * @param $var
     */
    function __construct(string $lib, string $file, $var = null)
    {
        $this->dicionario = null;
        $this->devLibrary = "http://dev.ontab.com.br";
        Helper::createFolderIfNoExist(PATH_HOME . "assetsPublic");
        Helper::createFolderIfNoExist(PATH_HOME . "assetsPublic/dist");
        Helper::createFolderIfNoExist(PATH_HOME . "assetsPublic/dist/route");
        $this->createParamDefault();

        $this->param = $this->getBaseParam($lib, $file);
        if (empty($this->param['title']))
            $this->param['title'] = $this->getTitle($lib, $file, $var);
        else
            $this->param['title'] = $this->prepareTitle($this->param['title'], $file);

        $this->createParam($this->param, $file);
        $this->param["vendor"] = VENDOR;
        $this->param["url"] = $file . (!empty($var) ? "/{$var}" : "");
        $this->param['loged'] = !empty($_SESSION['userlogin']);
        $this->param['login'] = ($this->param['loged'] ? $_SESSION['userlogin'] : "");
        $this->param['email'] = defined("EMAIL") && !empty(EMAIL) ? EMAIL : "contato@" . DOMINIO;
        $this->param['menu'] = "";
    }

    /**
     * @return array
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return mixed
     */
    public function getDicionario()
    {
        return $this->dicionario;
    }

    /**
     * @return mixed
     */
    public function getParam()
    {
        return $this->param;
    }

    /**
     * Cria os Parametros da página
     * @param array $f
     * @param string $file
     */
    private function createParam(array $f, string $file)
    {
        $this->createCoreJs($f['js'], 'dist/route/' . $file);
        $this->createCoreCss($f['css'], 'dist/route/' . $file);
        $this->createCoreFont($f['font'], $f['icon'], 'dist/route/' . $file);
    }

    /**
     * Cria os Cores defaults setados em Config
     */
    private function createParamDefault()
    {
        $f = ['js' => [], 'css' => [], 'font' => [], 'icon' => []];
        if (file_exists(PATH_HOME . "_config/param.json"))
            $f = json_decode(file_get_contents(PATH_HOME . "_config/param.json"), true);

        $this->createCoreJs($f['js'], 'dist/core');
        $this->createCoreCss($f['css'], 'dist/core');
        $this->createCoreFont($f['font'], $f['icon'], 'dist/fonts');
    }

    /**
     * @param string $lib
     * @param string $file
     * @return array
     */
    private function getBaseParam(string $lib, string $file)
    {
        $base = [
            "version" => VERSION,
            "meta" => "",
            "css" => [],
            "js" => [],
            "font" => "",
            "icon" => "",
            "descricao" => "",
            "analytics" => defined("ANALYTICS") ? ANALYTICS : ""
        ];

        $pathFile = ($lib === DOMINIO ? "" : VENDOR . "{$lib}/");
        if (file_exists(PATH_HOME . $pathFile . "param/{$file}.json"))
            $base = array_merge($base, json_decode(file_get_contents(PATH_HOME . ($lib === DOMINIO ? "" : VENDOR . "{$lib}/") . "param/{$file}.json"), true));

        if (file_exists(PATH_HOME . $pathFile . "assets/{$file}.js"))
            $base['js'][] = PATH_HOME . $pathFile . "assets/{$file}.js";

        if (file_exists(PATH_HOME . $pathFile . "assets/{$file}.css"))
            $base['css'][] = PATH_HOME . $pathFile . "assets/{$file}.css";

        return $base;
    }

    /**
     * @param string $lib
     * @param string $file
     * @param null $var
     * @return string
     */
    private function getTitle(string $lib, string $file, $var = null): string
    {
        $entity = str_replace("-", "_", $file);
        if (file_exists(PATH_HOME . "entity/cache/{$entity}.json") && $var) {
            return "";
            /*
            $this->dicionario = new Dicionario($entity);
            $where = "WHERE id = {$var}";
            if ($linkId = $this->dicionario->getInfo()['link']) {
                $where .= " || " . $this->dicionario->search($linkId)->getColumn() . " = '{$var}'";

                $read = new Read();
                $read->exeRead($entity, $where);
                if ($read->getResult()) {
                    return $read->getResult()[0][$this->dicionario->search($this->dicionario->getInfo()['title'])->getColumn()] . " | " . SITENAME;
                }
            }*/
        }

        return ($file === "index" ? SITENAME . (defined('SITESUB') && !empty(SITESUB) ? " | " . SITESUB : "") : ucwords(str_replace(['-', "_"], " ", $file)) . " | " . SITENAME);
    }

    /**
     * @param array $jsList
     * @param string $name
     */
    private function createCoreJs(array $jsList, string $name = "core")
    {
        if (PRODUCAO && file_exists(PATH_HOME . "assetsPublic/{$name}.min.js")) {
            $this->param['js'] = [HOME . "assetsPublic/{$name}.min.js"];

        } elseif (!empty($jsList)) {
            $minifier = new Minify\JS("");
            foreach ($jsList as $js) {
                if (!preg_match('/\//i', $js))
                    $minifier->add(PATH_HOME . $this->checkAssetsExist($js, "js"));
                else
                    $minifier->add($js);
            }

            $minifier->minify(PATH_HOME . "assetsPublic/{$name}.min.js");
            $this->param['js'] = [HOME . "assetsPublic/{$name}.min.js"];
        }
    }

    /**
     * @param array $cssList
     * @param string $name
     */
    private function createCoreCss(array $cssList, string $name = "core")
    {
        if (PRODUCAO && file_exists(PATH_HOME . "assetsPublic/{$name}.min.css")) {
            $this->param['css'] = file_get_contents(PATH_HOME . "assetsPublic/{$name}.min.css");

        } elseif (!empty($cssList)) {
            $minifier = new Minify\CSS("");
            $minifier->setMaxImportSize(30);

            foreach ($cssList as $css) {
                if (!preg_match('/\//i', $css))
                    $minifier->add(PATH_HOME . $this->checkAssetsExist($css, "css"));
                else
                    $minifier->add($css);
            }

            $minifier->minify(PATH_HOME . "assetsPublic/{$name}.min.css");
            $this->param['css'] = $minifier->minify();
        }
    }

    /**
     * @param $fontList
     * @param null $iconList
     * @param string $name
     */
    private function createCoreFont($fontList, $iconList = null, string $name = 'fonts')
    {
        if ((!empty($fontList) || !empty($iconList)) && !file_exists(PATH_HOME . "assetsPublic/{$name}.min.css")) {
            $fonts = "";
            if (!empty($fontList)) {
                foreach ($fontList as $item)
                    $fonts .= $this->getFontIcon($item, "font");
            }
            if (!empty($iconList)) {
                foreach ($iconList as $item)
                    $fonts .= $this->getFontIcon($item, "icon");
            }

            $m = new Minify\CSS($fonts);
            $m->minify(PATH_HOME . "assetsPublic/{$name}.min.css");
            $this->param['css'] .= $m->minify();
        }
    }

    /**
     * Prepara o formato do título caso tenha variáveis
     *
     * @param string $title
     * @param string $file
     * @return string
     */
    private function prepareTitle(string $title, string $file): string
    {
        if (preg_match('/{{/i', $title)) {
            $data = [
                "sitename" => SITENAME,
                "SITENAME" => SITENAME,
                "sitesub" => SITESUB,
                "SITESUB" => SITESUB,
                "title" => !empty($this->dicionario) ? $this->dicionario->getRelevant()->getValue() : ucwords(str_replace(['-', "_"], " ", $file)),
                "file" => ucwords(str_replace(['-', "_"], " ", $file))
            ];

            foreach (explode('{{', $title) as $i => $item) {
                if ($i > 0) {
                    $variavel = explode('}}', $item)[0];
                    $title = str_replace('{{' . $variavel . '}}', (!empty($data[$variavel]) ? $data[$variavel] : ""), $title);
                }
            }
        }
        return $title;
    }

    /**
     * @param string $item
     * @param string $tipo
     * @return string
     */
    private function getFontIcon(string $item, string $tipo): string
    {
        $data = "";
        $urlOnline = $tipo === "font" ? "https://fonts.googleapis.com/css?family=" . ucfirst($item) . ":100,300,400,700" : "https://fonts.googleapis.com/icon?family=" . ucfirst($item) . "+Icons";
        if (Validate::online($urlOnline)) {
            $data = file_get_contents($urlOnline);
            foreach (explode('url(', $data) as $i => $u) {
                if ($i > 0) {
                    $url = explode(')', $u)[0];
                    if (!file_exists(PATH_HOME . "assetsPublic/fonts/" . pathinfo($url, PATHINFO_BASENAME))) {
                        if (Validate::online($url)) {
                            Helper::createFolderIfNoExist(PATH_HOME . "assetsPublic/fonts");
                            $f = fopen(PATH_HOME . "assetsPublic/fonts/" . pathinfo($url, PATHINFO_BASENAME), "w+");
                            fwrite($f, file_get_contents($url));
                            fclose($f);
                            $data = str_replace($url, HOME . "assetsPublic/fonts/" . pathinfo($url, PATHINFO_BASENAME), $data);
                        } else {
                            $before = "@font-face" . explode("@font-face", $u[$i - 1])[1] . "url(";
                            $after = explode("}", $u)[0];
                            $data = str_replace($before . $after, "", $data);
                        }
                    } else {
                        $data = str_replace($url, HOME . "assetsPublic/fonts/" . pathinfo($url, PATHINFO_BASENAME), $data);
                    }
                }
            }
        }
        return $data;
    }

    /**
     * Verifica se uma lib existe no sistema, se não existir, baixa do server
     *
     * @param string $lib
     * @param string $extensao
     * @return string
     */
    private function checkAssetsExist(string $lib, string $extensao): string
    {
        if (!file_exists("assetsPublic/{$lib}/{$lib}.min.{$extensao}")) {
            $this->createFolderAssetsLibraries("assetsPublic/{$lib}/{$lib}.min.{$extensao}");
            if (!Validate::online("{$this->devLibrary}/{$lib}/{$lib}" . ".{$extensao}"))
                return "";

            if ($extensao === 'js')
                $mini = new Minify\JS(file_get_contents("{$this->devLibrary}/{$lib}/{$lib}" . ".{$extensao}"));
            else
                $mini = new Minify\CSS($this->preperaCss("{$this->devLibrary}/{$lib}/{$lib}" . ".{$extensao}", $lib));

            $mini->minify(PATH_HOME . "assetsPublic/{$lib}/{$lib}.min.{$extensao}");
        }

        return "assetsPublic/{$lib}/{$lib}.min.{$extensao}";
    }

    /**
     * @param string $url
     * @param string $lib
     * @return mixed|string
     */
    private function preperaCss(string $url, string $lib)
    {
        if (!in_array($lib, ["app", "normalize", "panel", "theme", "boot"])) {
            $m = new Minify\CSS(file_get_contents($url));
            $content = $m->minify();
            $tags = ['nav', 'section', 'aside', 'ul', 'li', 'img', 'i'];

            $el = explode('{', $content);
            $content2 = $content;
            foreach ($el as $i => $e) {
                $c = "}";
                if (preg_match('/}/i', $e)) {
                    $item = explode('}', $e);
                    $item = trim($item[count($item) -1]);
                } else {
                    $item = trim($e);
                    $c = "";
                }
                if (!preg_match('/^@/i', $item)) {
                    $t = explode(',', $item);
                    foreach ($t as $l => $it) {
                        if (!empty($it)) {
                            if (in_array(trim($it), $tags) || preg_match("/^(" . implode('|', $tags) . ")(:|\s)/i", trim($it)))
                                $t[$l] = "#single-content " . trim($it);
                            elseif (trim($it) === "*" || preg_match("/^\*(:|\s)/i", trim($it)))
                                $t[$l] = "";
                            elseif (trim($it) === 'body')
                                $t[$l] = "#single-content";
                            elseif (preg_match("/^body(:|\s)/i", trim($it)))
                                $t[$l] = preg_replace("/^(html|body)(:|\s)/",'#single-content\1', trim($it));
                        }
                    }
                    $base = implode(',', array_filter($t));

                    if (empty($base) && isset($el[$i + 1])) {
                        $content2 = str_replace((!empty($c) ? $item : $e) . '{' . explode('}', $el[$i + 1])[0] . '}', '', $content2);
                    } elseif ($item !== $base) {
                        $content2 = str_replace("{$c}{$item}{", "{$c}{$base}{", $content2);
                    }
                }
            }

            return $content2;
        } else {
            return file_get_contents($url);
        }
    }

    /**
     * @param string $file
     */
    private function createFolderAssetsLibraries(string $file)
    {
        $link = PATH_HOME;
        $split = explode('/', $file);
        foreach ($split as $i => $peca) {
            if ($i < count($split) - 1) {
                $link .= ($i > 0 ? "/" : "") . $peca;
                Helper::createFolderIfNoExist($link);
            }
        }
    }
}