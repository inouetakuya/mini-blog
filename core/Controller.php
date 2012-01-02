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
}
