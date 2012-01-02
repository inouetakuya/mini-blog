<?php

/**
 * Application.
 *
 * Request クラスや Router クラス、Response クラス、
 * Session クラスなどのオブジェクトの管理を行うほか、
 * ルーティングの定義、コントローラの実行、レスポンスの送信など、
 * アプリケーション全体の流れを司る
 *
 * また、管理するのはオブジェクトだけではなく、
 * アプリケーションの様々なディレクトリヘのパスの管理なども行う
 *
 * この他にも、デバッグモードで実行できるような機能も持たせます
 *
 * @author Katsuhiro Ogawa <fivestar@nequal.jp>
 */
abstract class Application
{
    protected $debug = false;
    protected $request;
    protected $response;
    protected $session;
    protected $db_manager;

    /**
     * コンストラクタ
     *
     * @param boolean $debug
     */
    public function __construct($debug = false)
    {
        $this->setDebugMode($debug);
        $this->initialize();
        $this->configure();
    }

    /**
     * デバッグモードを設定
     *
     * エラー表示処理を変更する
     * 
     * @param boolean $debug
     */
    protected function setDebugMode($debug)
    {
        if ($debug) {
            $this->debug = true;
            ini_set('display_errors', 1);
            error_reporting(-1);
        } else {
            $this->debug = false;
            ini_set('display_errors', 0);
        }
    }

    /**
     * アプリケーションの初期化
     */
    protected function initialize()
    {
        $this->request    = new Request();
        $this->response   = new Response();
        $this->session    = new Session();
        $this->db_manager = new DbManager();
        $this->router     = new Router($this->registerRoutes());
    }

    /**
     * アプリケーションの設定
     *
     * 個別のアプリケーションで様々な設定をできるように定義
     */
    protected function configure()
    {
    }

    /**
     * プロジェクトのルートディレクトリを取得
     *
     * @return string ルートディレクトリへのファイルシステム上の絶対パス
     */
    abstract public function getRootDir();

    /**
     * ルーティングを取得
     *
     * 抽象メソッドとして定義しておけば呼び出し側は変える必要がなく、
     * 個別のアプリケーションで registerRoutes メソッドを
     * 定義しないとエラーになるため定義漏れもなくなる
     *
     * @return array
     */
    abstract protected function registerRoutes();

    /**
     * デバッグモードか判定
     *
     * @return boolean
     */
    public function isDebugMode()
    {
        return $this->debug;
    }

    /**
     * Requestオブジェクトを取得
     *
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Responseオブジェクトを取得
     *
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Sessionオブジェクトを取得
     *
     * @return Session
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * DbManagerオブジェクトを取得
     *
     * @return DbManager
     */
    public function getDbManager()
    {
        return $this->db_manager;
    }

    /**
     * コントローラファイルが格納されているディレクトリへのパスを取得
     *
     * @return string
     */
    public function getControllerDir()
    {
        return $this->getRootDir() . '/controllers';
    }

    /**
     * ビューファイルが格納されているディレクトリへのパスを取得
     *
     * @return string
     */
    public function getViewDir()
    {
        return $this->getRootDir() . '/views';
    }

    /**
     * モデルファイルが格納されているディレクトリへのパスを取得
     *
     * @return stirng
     */
    public function getModelDir()
    {
        return $this->getRootDir() . '/models';
    }

    /**
     * ドキュメントルートへのパスを取得
     *
     * @return string
     */
    public function getWebDir()
    {
        return $this->getRootDir() . '/web';
    }

    /**
     * アプリケーションを実行する
     *
     * Router クラスの resolve メソッドを呼び出して
     * ルーティングパラメータを取得し、コントローラ名とアクション名を特定
     * それらの値を元に runAction メソッドを呼び出してアクションを実行
     *
     * @throws HttpNotFoundException ルートが見つからない場合
     */
    public function run()
    {
        try {
            $params = $this->router->resolve($this->request->getPathInfo());
            if ($params === false) {
                throw new HttpNotFoundException('No route found for ' . $this->request->getPathInfo());
            }

            $controller = $params['controller'];
            $action = $params['action'];

            $this->runAction($controller, $action, $params);
        } catch (HttpNotFoundException $e) {
            $this->render404Page($e);
        } catch (UnauthorizedActionException $e) {
            list($controller, $action) = $this->login_action;
            $this->runAction($controller, $action);
        }

        $this->response->send();
    }

    /**
     * 指定されたアクションを実行する
     *
     * @param string $controller_name
     * @param string $action
     * @param array $params
     *
     * @throws HttpNotFoundException コントローラが特定できない場合
     */
    public function runAction($controller_name, $action, $params = array())
    {
        $controller_class = ucfirst($controller_name) . 'Controller';

        $controller = $this->findController($controller_class);
        if ($controller === false) {

            // 例外クラスコンストラクタの第1引数はエラーメッセージ
            // デバッグを行いやすくするためにも、状況に応じて適切なエラーメッセージを指定
            throw new HttpNotFoundException($controller_class . ' controller is not found.');
        }

        $content = $controller->run($action, $params);

        $this->response->setContent($content);
    }

    /**
     * 指定されたコントローラ名から対応するControllerオブジェクトを取得
     *
     * @param string $controller_class
     * @return Controller
     */
    protected function findController($controller_class)
    {
        if (!class_exists($controller_class)) {
            $controller_file = $this->getControllerDir() . '/' . $controller_class . '.php';
            if (!is_readable($controller_file)) {
                return false;
            } else {
                require_once $controller_file;

                if (!class_exists($controller_class)) {
                    return false;
                }
            }
        }

        // コンストラクタには Application クラス自身を渡す
        return new $controller_class($this);
    }

    /**
     * 404エラー画面を返す設定
     *
     * @param Exception $e
     */
    protected function render404Page($e)
    {
        $this->response->setStatusCode(404, 'Not Found');
        $message = $this->isDebugMode() ? $e->getMessage() : 'Page not found.';
        $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

        $this->response->setContent(<<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>404</title>
</head>
<body>
    {$message}
</body>
</html>
EOF
        );
    }
}
