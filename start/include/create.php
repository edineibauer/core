<?php

/**
 * Cria Diretório
 * @param string $dir
 * @return string
 */
function createDir(string $dir)
{
    if (!file_exists("../../../{$dir}"))
        mkdir("../../../{$dir}", 0777);

    return "../../../{$dir}";
}

/**
 * Cria Arquivo
 * @param string $file
 * @param string $content
 */
function writeFile(string $file, string $content)
{
    $fp = fopen("../../../{$file}", "w+");
    fwrite($fp, $content);
    fclose($fp);
}

/**
 * @param array $dados
 * @return array
 */
function getServerConstants(array $dados)
{
    $localhost = ($_SERVER['SERVER_NAME'] === "localhost" ? true : false);

    $dados['sitesub'] = "";
    $dados['dominio'] = ($localhost ? explode('/', $_SERVER['REQUEST_URI'])[1] : $_SERVER['SERVER_NAME']);
    $dados['protocol'] = (isset($dados['protocol']) && $dados['protocol'] ? 'https://' : 'http://');
    $dados['www'] = isset($dados['www']) && $dados['www'] ? "www" : "";
    $dados['home'] = $dados['protocol'] . ($localhost ? 'localhost/' : '') . $dados['dominio'] . "/";
    $dados['path_home'] = ($_SERVER['DOCUMENT_ROOT'] . ($localhost ? DIRECTORY_SEPARATOR . $dados['dominio'] : "") . "/");
    $dados['logo'] = (!empty($_FILES['logo']['name']) ? 'uploads/site/' . $_FILES['logo']['name'] : "");
    $dados['favicon'] = 'uploads/site/' . $_FILES['favicon']['name'];
    $dados['vendor'] = "vendor/singular/";
    $dados['version'] = "1.00";

    return $dados;
}

/**
 * Realiza uploads da logo e favicon
 */
function uploadFiles()
{
    if (!empty($_FILES['logo']['name']) && preg_match('/^image\//i', $_FILES['logo']['type']))
        move_uploaded_file($_FILES['logo']['tmp_name'], "../../../uploads/site/" . basename($_FILES['logo']['name']));

    if (preg_match('/^image\//i', $_FILES['favicon']['type']))
        move_uploaded_file($_FILES['favicon']['tmp_name'], "../../../uploads/site/" . basename($_FILES['favicon']['name']));
}

/**
 * Criar Arquivo de Configurações
 * @param array $dados
 */
function createConfig(array $dados)
{
    $conf = "<?php\n";
    foreach ($dados as $dado => $value) {
        $value = (is_bool($value) ? ($value ? 'true' : 'false') : "'{$value}'");
        $conf .= "define('" . strtoupper(trim($dado)) . "', {$value});\n";
    }

    writeFile("_config/config.php", $conf);
}

/**
 * Cria Arquivo de Rota e adiciona o atual domínio como uma rota alteranativa
 * @param array $dados
 */
function createRoute(array $dados)
{
    $data = json_decode(file_get_contents("start/tpl/routes.json"), true);
    if (!in_array($dados['dominio'], $data))
        $data[] = $dados['dominio'];

    writeFile("_config/route.json", json_encode($data));
}

/**
 * Cria Arquivo de Parâmetros Padrões do Sistema Singular
 * @param array $dados
 */
function createParam(array $dados)
{
    $data = str_replace('{$sitename}', $dados['sitename'], file_get_contents("start/tpl/param.json"));
    writeFile("_config/param.json", $data);
}

/**
 * Cria Arquivo de Manifest e Service Worker para PWA
 * @param array $dados
 */
function createManifest(array $dados) {
    $data = str_replace(['{$sitename}', '{$favicon}', '{$theme}', '{$themeColor}'], [$dados['sitename'], $dados['favicon'], '#2196f3', '#FFFFFF'], file_get_contents("start/tpl/manifest.txt"));
    writeFile("manifest.json", $data);
    writeFile("service-worker.js", file_get_contents("start/tpl/service-worker.txt"));
}

/**
 * @param array $data
 * @param string $domain
 * @param string $www
 * @param string $protocol
 */
function createHtaccess(array $data, string $domain, string $www, string $protocol)
{
    $dados = "RewriteCond %{HTTP_HOST} ^" . ($www === "www" ? "{$domain}\nRewriteRule ^ {$protocol}://www.{$domain}%{REQUEST_URI}" : "www.(.*) [NC]\nRewriteRule ^(.*) {$protocol}://%1/$1") . " [L,R=301]";
    writeFile(".htaccess", str_replace(['{$dados}', '{$home}'], [$dados, $data['home']], file_get_contents("start/tpl/htaccess.txt")));
}

function getAccessFile()
{
    return '<Files "*.json">
            Order Deny,Allow
            Deny from all
        </Files>
        <Files "*.php">
            Order Deny,Allow
            Deny from all
        </Files>
        <Files "*.html">
            Order Deny,Allow
            Deny from all
        </Files>
        <Files "*.tpl">
            Order Deny,Allow
            Deny from all
        </Files>';
}

if (!empty($dados['sitename']) && !empty($_FILES['favicon']['name'])) {
    $dados = getServerConstants($dados);

    //Create Dir
    createDir("entity");
    createDir("entity/general");
    createDir("uploads");
    createDir("uploads/site");
    createDir("_config");
    createDir("ajax");
    createDir("view");
    createDir("tpl");

    uploadFiles();
    createConfig($dados);
    createRoute($dados);
    createParam($dados);
    createManifest($dados);

    writeFile("index.php", file_get_contents("start/tpl/index.txt"));
    writeFile("tim.php", file_get_contents("start/tpl/tim.txt"));
    writeFile("_config/entity_not_show.json", '{"1":[],"2":[],"3":[],"0":[]}');
    writeFile("_config/menu_not_show.json", '{"1":[],"2":[],"3":[],"0":[]}');
    writeFile("entity/general/general_info.json", "[]");
    writeFile("_config/.htaccess", "Deny from all");
    writeFile("entity/.htaccess", "Deny from all");
    writeFile("ajax/.htaccess", "Deny from all");
    writeFile("view/.htaccess", "Deny from all");
    writeFile("tpl/.htaccess", "Deny from all");
    writeFile("vendor/.htaccess", getAccessFile());

    createHtaccess($dados, $dados['dominio'], $dados['www'], $dados['protocol']);

    header("Location: ../../../libsUpdate");
} else {
    require_once 'erroForm.php';
    require_once 'form.php';
}