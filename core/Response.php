<?php

/**
 * Response.
 *
 * HTTP ヘッダを送信するための header() 関数が PHP には実装されており、
 * 好きなところから HTTP ヘッダの送信が可能ですが、管理をしやすくするために
 * HTTP ヘッダの情報はすべて Response クラスで扱う
 *
 * @author Katsuhiro Ogawa <fivestar@nequal.jp>
 */
class Response
{
    protected $content;
    protected $status_code = 200;
    protected $status_text = 'OK';
    protected $http_headers = array();

    /**
     * レスポンスを送信
     */
    public function send()
    {
        header('HTTP/1.1 ' . $this->status_code . ' ' . $this->status_text);

        foreach ($this->http_headers as $name => $value) {
            header($name . ': ' . $value);
        }

        echo $this->content;
    }

    /**
     * コンテンツを設定
     *
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * ステータスコードを設定
     *
     * 404 Not Found, 500 Internal Server Error など
     * 可能ならばすべてのステータスコードとテキストを Response クラス内に
     * 保持したいが、分量が多いため、テキスト指定する
     *
     * @param integer $status_code
     * @param string $status_code
     */
    public function setStatusCode($status_code, $status_text = '')
    {
        $this->status_code = $status_code;
        $this->status_text = $status_text;
    }

    /**
     * HTTPレスポンスヘッダを設定
     *
     * @param string $name
     * @param mixed $value
     */
    public function setHttpHeader($name, $value)
    {
        $this->http_headers[$name] = $value;
    }
}
