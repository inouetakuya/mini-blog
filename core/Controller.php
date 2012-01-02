<?php

/**
 * Controller.
 *
 * 一般的な MVC モデルでのコントローラはビューファイルにデータを
 * 渡す部分までを担い、ビューファイルのレンダリングは別のクラスが行う
 *
 * 今回コントローラにビューファイルをレンダリングする機能を
 * 持たせているのは、ビューファイルのレンダリングを
 * コントローラ内で行えた方が、たとえばレンダリング結果をファイルに
 * 保存してリダイレクトする、などといった処理を行う上で都合がよいため
 *
 * アクションを実行する run メソッド
 * ビューファイルをレンダリングする render メソッド
 * 内部で View クラスを呼び出す
 * リダイレクトを行う redirect メソッド
 * 404 エラー画面に遷移する forward404 メソッド
 * CSRF 対策を行う generateCsrfroken メソッドと checkCsrfToken
 * ログイン状態の制御機構
 * run メソッドの内部で行う
 *
 * @author Katsuhiro Ogawa <fivestar@nequal.jp>
 */
abstract class Controller
{
    protected $controller_name;
    protected $action_name;
    protected $application;
    protected $request;
    protected $response;
    protected $session;
    protected $db_manager;
    protected $auth_actions = array();

    /**
     * コンストラクタ
     *
     * コンストラクタに Application クラス自身を渡すようにしている
     * Request や Response といったクラスは Application クラスが
     * 持っているため
     *
     * @param Application $application
     */
    public function __construct($application)
    {
        // 「Controller」が10文字なので後ろの10文字分を取り除く
        $this->controller_name = strtolower(substr(get_class($this), 0, -10));

        $this->application = $application;
        $this->request     = $application->getRequest();
        $this->response    = $application->getResponse();
        $this->session     = $application->getSession();
        $this->db_manager  = $application->getDbManager();
    }

    /**
     * アクションを実行
     *
     * アクションにあたるメソッド名は「アクション名 + Action」というルールで扱う
     *
     * @param string $action
     * @param array $params
     * @return string レスポンスとして返すコンテンツ
     *
     * @throws UnauthorizedActionException 認証が必須なアクションに認証前にアクセスした場合
     */
    public function run($action, $params = array())
    {
        $this->action_name = $action;

        // アクションにあたるメソッド名は「アクション名 + Action」というルールで扱う
        $action_method = $action . 'Action';
        if (!method_exists($this, $action_method)) {
            $this->forward404();
        }

        if ($this->needsAuthentication($action) && !$this->session->isAuthenticated()) {
            throw new UnauthorizedActionException();
        }

        $content = $this->$action_method($params);

        return $content;
    }

    /**
     * ビューファイルのレンダリング
     *
     * @param array $variables テンプレートに渡す変数の連想配列
     * @param string $template ビューファイル名(nullの場合はアクション名を使う)
     * @param string $layout レイアウトファイル名
     * @return string レンダリングしたビューファイルの内容
     */
    protected function render($variables = array(), $template = null, $layout = 'layout')
    {
        $defaults = array(
            'request'  => $this->request,
            'base_url' => $this->request->getBaseUrl(),
            'session'  => $this->session,
        );

        $view = new View($this->application->getViewDir(), $defaults);

        if (is_null($template)) {
            $template = $this->action_name;
        }

        $path = $this->controller_name . '/' .$template;

        return $view->render($path, $variables, $layout);
    }

    /**
     * 404エラー画面を出力
     *
     * @throws HttpNotFoundException
     */
    protected function forward404()
    {
        throw new HttpNotFoundException('Forwarded 404 page from '
            . $this->controller_name . '/' . $this->action_name);
    }

    /**
     * 指定されたURLへリダイレクト
     *
     * @param string $url
     */
    protected function redirect($url)
    {
        // 同じアプリケーション内で別アクションのリダイレクトを
        // 行うときは PATH_INFO 部分のみ指定すればいいようにする
        if (!preg_match('#https?://#', $url)) {
            $protocol = $this->request->isSsl() ? 'https://' : 'http://';
            $host = $this->request->getHost();
            $base_url = $this->request->getBaseUrl();

            $url = $protocol . $host . $base_url . $url;
        }

        // 302 はブラウザにリダイレクトを伝えるためのステータスコード
        $this->response->setStatusCode(302, 'Found');
        $this->response->setHttpHeader('Location', $url);
    }

    /**
     * CSRFトークンを生成
     *
     * トークンを生成し、セッションに格納した上でトークンを返す
     * トークンをフォームごとに識別する
     *
     * @param string $form_name
     * @return string $token
     */
    protected function generateCsrfToken($form_name)
    {
        $key = 'csrf_tokens/' . $form_name;
        $tokens = $this->session->get($key, array());

        // 同一アクションを複数画面開いたときの対応
        if (count($tokens) >= 10) {
            array_shift($tokens); // 古いものから削除する
        }

        $token = sha1($form_name . session_id() . microtime());
        $tokens[] = $token;

        $this->session->set($key, $tokens);

        return $token;
    }

    /**
     * CSRFトークンが妥当かチェック
     *
     * リクエストされてきたトークンとセッションに格納されたトークンを
     * 比較した結果を返し、同時にセッションからトークンを削除する
     *
     * @param string $form_name
     * @param string $token
     * @return boolean
     */
    protected function checkCsrfToken($form_name, $token)
    {
        $key = 'csrf_tokens/' . $form_name;
        $tokens = $this->session->get($key, array());

        // セッション上にトークンが格納されているかを判定する
        if (false !== ($pos = array_search($token, $tokens, true))) {
            unset($tokens[$pos]);
            $this->session->set($key, $tokens);

            // 処理を継続してよいことを伝えるために true を返す
            return true;
        }

        return false;
    }
}
