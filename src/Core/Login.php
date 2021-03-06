<?php

namespace Core;

use EntityForm\Dicionario;
use Helper\Convert;
use \ReCaptcha\ReCaptcha;

class Login
{
    private $email;
    private $senha;
    private $recaptcha;
    private $attempts = 0;
    private $result;

    /**
     * @param mixed $data
     */
    public function __construct($data = null)
    {
        if ($data) {
            if (isset($data['recaptcha']) && !empty($data['recaptcha']))
                $this->setRecaptcha($data['recaptcha']);
            if (isset($data['email']) && !empty($data['email']))
                $this->setEmail($data['email']);
            if (isset($data['password']) && !empty($data['password']))
                $this->setSenha($data['password']);
        }
    }

    /**
     * @param mixed $result
     */
    public function setResult($result)
    {
        $this->result = $result;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        if (!empty($email))
            $this->email = (string)strip_tags(trim($email));
        $this->start();
    }

    /**
     * @param string $senha
     */
    public function setSenha($senha)
    {
        if (!empty($senha) && strlen($senha) > 3)
            $this->senha = (string)Convert::password(trim($senha));
        else
            $this->setResult('Senha muito Curta');

        $this->start();
    }

    /**
     * @param mixed $recaptcha
     */
    public function setRecaptcha($recaptcha)
    {
        $this->recaptcha = $recaptcha;
        $this->start();
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    public function logOut()
    {
        setcookie("token", 0, time() - 1, "/");
        if (isset($_SESSION['userlogin'])) {
            if(isset($_SESSION['userlogin']['token']) && !empty($_SESSION['userlogin']['token'])) {
                $token = new TableCrud(PRE . "usuarios");
                $token->load("token", $_SESSION['userlogin']['token']);
                if ($token->exist()) {
                    $token->setDados(["token" => null, "token_expira" => null]);
                    $token->save();
                }
            }

            session_unset();
        }
    }

    private function start()
    {
        if ($this->email && $this->senha && !$this->attemptExceded()) {
            if (LOGGED)
                $this->setResult('Você já esta logado.');
            elseif ($this->isHuman())
                $this->checkUserInfo();

        } elseif ($this->email && $this->senha) {
            $cont = 10 - $this->attempts;
            $this->setResult($cont > 0 ? "{$cont} tentativas faltantes" : " bloqueado por 15 minutos");
        }
    }

    /**
     * Vetifica usuário e senha no banco de dados!
     */
    private function checkUserInfo()
    {
        if(!$this->getResult()) {
            $d = new Dicionario("usuarios");
            $emailName = $d->search($d->getInfo()['email'])->getColumn();
            $linkName = $d->search($d->getInfo()['link'])->getColumn();
            $password = $d->search($d->getInfo()['password'])->getColumn();
            $nomeName = $d->getRelevant()->getColumn();

            $read = new Read();
            $read->exeRead(PRE . "usuarios", "WHERE ({$emailName} = :email || {$linkName} = :email || {$nomeName} = :email) && {$password} = :pass", "email={$this->email}&pass={$this->senha}");
            if ($read->getResult() && $read->getResult()[0]['status'] === '1' && !$this->getResult()) {
                $_SESSION['userlogin'] = $read->getResult()[0];

                if (!isset($_SESSION['userlogin']['nome']))
                    $_SESSION['userlogin']['nome'] = $_SESSION['userlogin'][$nomeName];

                if (!isset($_SESSION['userlogin']['nome_usuario']))
                    $_SESSION['userlogin']['nome_usuario'] = $_SESSION['userlogin'][$linkName];

                if (!isset($_SESSION['userlogin']['email']))
                    $_SESSION['userlogin']['email'] = $_SESSION['userlogin'][$emailName];

                $_SESSION['userlogin']['imagem'] = json_decode($_SESSION['userlogin']['imagem'], true)[0]['url'];

                $up = new Update();
                $up->exeUpdate(PRE . "usuarios", ['token' => $this->getToken(), "token_expira" => date("Y-m-d H:i:s"), "token_recovery" => null], "WHERE (email = :email || nome_usuario = :email) && password = :pass", "email={$this->email}&pass={$this->senha}");
                $this->setCookie($_SESSION['userlogin']['token']);
            } else {
                if ($read->getResult())
                    $this->setResult('Usuário Desativado!');
                else
                    $this->setResult('Login Inválido!');

                $attempt = new TableCrud("login_attempt");
                $attempt->loadArray(array("ip" => filter_var(Helper::getIP(), FILTER_VALIDATE_IP), "data" => date("Y-m-d H:i:s"), "username" => $this->email));
                $attempt->save();
            }
        }
    }

    private function attemptExceded()
    {
        $ip = filter_var(Helper::getIP(), FILTER_VALIDATE_IP);
        $read = new Read();
        $read->exeRead(PRE . "login_attempt", "WHERE data > DATE_SUB(NOW(), INTERVAL 15 MINUTE) && ip = '{$ip}' && email = '{$this->email}'");
        $this->attempts = $read->getRowCount();

        return ($this->attempts > 10); // maximo de 10 tentativas por IP e email iguais em um intervalo de 15 minutos
    }

    private function isHuman()
    {
        if (defined("RECAPTCHA") && $this->attempts < 6) {
            if (empty($this->recaptcha))
                $this->setResult("resolva o captcha");

            $recaptcha = new ReCaptcha(RECAPTCHA);
            $resp = $recaptcha->verify($this->recaptcha, filter_var(Helper::getIP(), FILTER_VALIDATE_IP));
            if (!$resp->isSuccess())
                $this->setResult('<p>' . implode('</p><p>', $resp->getErrorCodes()) . '</p>');
        }

        return $this->getResult() ? false : true;
    }

    private function setCookie($token)
    {
        setcookie("token", $token, time() + (86400 * 30 * 2), "/"); // 2 meses de cookie
    }

    /**
     * @return string
     */
    private function getToken()
    {
        return md5("tokes" . rand(9999, 99999) . md5(base64_encode(date("Y-m-d H:i:s"))) . rand(0, 9999));
    }
}
