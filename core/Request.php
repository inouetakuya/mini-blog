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

    /**
     * ベース URL（ホスト部分より後ろから、フロントコントローラまでの値）
     *
     * index.php が http://example.com/foo/bar/index.php にあるとき
     * http://example.com/foo/bar/list の「/foo/bar」の部分
     *
     * HTML 内のリンクを作成する際に利用する
     * フロントコントローラを用いているため、1つ前のディレクトリにある
     * ファイル（../xxx.php）のような指定ができないため、リンクには
     * ドキュメントルート以下の絶対 URL を指定する必要がある
     *
     * REQUEST_URI（ホスト部分より後ろ）: /foo/bar/list
     * SCRIPT_NAME（フロントコントローラまでのパス）: /foo/bar/index.php
     * ベース URL（ホスト部分より後ろから、フロントコントローラまでの値）: /foo/bar
     * PATH_INFO（ベース URL より後ろの値）: /list
     */
    public function getBaseUrl()
    {
        $script_name = $_SERVER['SCRIPT_NAME'];
        $request_uri = $this->getRequestUri();

        // フロントコントローラが URL に含まれるとき
        if (0 === strpos($request_uri, $script_name)) {
            return $script_name;

        // フロントコントローラが省略されているとき
        } else if (0 === strpos($request_uri, dirname($script_name))) {
            return rtrim(dirname($script_name), '/');
        }

        return '';
    }

    /**
     * PATH_INFO（ベース URL より後ろの値）
     *
     * index.php が http://example.com/foo/bar/index.php にあるとき
     * http://example.com/foo/bar/list の「/list」の部分
     */
    public function getPathInfo()
    {
        $base_url = $this->getBaseUrl();
        $request_uri = $this->getRequestUri();

        // REQUEST_URI に含まれる GET パラメータを取り除く
        if (false !== ($pos = strpos($request_uri, '?'))) {
            $request_uri = substr($request_uri, 0, $pos);
        }

        $path_info = (string)substr($request_uri, strlen($base_url));

        return $path_info;
    }
}
