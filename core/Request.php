<?php
/**
 * $_SERVER や $_POST といったスーパーグローバル変数の存在をカプセル化する
 * こうすることでテストを行いたい場合など、より柔軟に対応できる
 *
 * Created by JetBrains PhpStorm.
 * User: inouetakuya
 * Date: 12/01/01
 * Time: 18:14
 * To change this template use File | Settings | File Templates.
 */
class Request
{
    public function isPost()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return true;
        }

        return false;
    }

    public function getGet($name, $default = null)
    {
        if (isset($_GET[$name])) {
            return $_GET[$name];
        }

        return $default;
    }

    public function getPost($name, $defalut = null)
    {
        if (isset($_POST[$name])) {
            return $_POST[$name];
        }

        return $defalut;
    }

    /**
     * サーバのホスト名はリダイレクトを行う場合などに利用する
     */
    public function getHost()
    {
        if (!empty($_SERVER['HTTP_HOST'])) {
            return $_SERVER['HTTP_HOST'];
        }

        return $_SERVER['SERVER_NAME'];
    }

    public function isSsl()
    {
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            return true;
        }

        return false;
    }

    /**
     * URL のホスト部分以降の値を返す
     */
    public function getRequestUri()
    {
        return $_SERVER['REQUEST_URI'];
    }
}
