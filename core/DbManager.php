<?php

/**
 * DbManager.
 *
 * PDO クラスのインスタンスがデータベースとの接続情報になるので、
 * DbManager ではその管理を行う
 *
 * @author Katsuhiro Ogawa <fivestar@nequal.jp>
 */
class DbManager
{
    // 接続情報である PDO クラスのインスタンスを配列で保持する
    protected $connections = array();

    protected $repository_connection_map = array();
    protected $repositories = array();

    /**
     * データベースへ接続
     *
     * @param string $name
     * @param array $params
     */
    public function connect($name, $params)
    {
        // array merge 関数を使っているのは、後ほどこの $params 配列から値を
        // 取り出す際にキーが存在するかのチェックをしないで済むようにするため
        $params = array_merge(array(
            'dsn'      => null,
            'user'     => '',
            'password' => '',
            'options'  => array(),
        ), $params); // P501 連想キーが重複する場合には上書きされる

        $con = new PDO(
            $params['dsn'],
            $params['user'],
            $params['password'],
            $params['options']
        );

        // PDO の内部でエラーが起きた場合に例外を発生させるようにするため
        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // 接続情報である PDO クラスのインスタンスを配列で保持する
        $this->connections[$name] = $con;
    }

    /**
     * コネクションを取得
     *
     * @string $name
     * @return PDO
     */
    public function getConnection($name = null)
    {
        if (is_null($name)) {
            // 指定がなければ最初に作成した PDO クラスのインスタンスを返す
            return current($this->connections);
        }

        return $this->connections[$name];
    }

    /**
     * リポジトリごとのコネクション情報を設定
     *
     * @param string $repository_name
     * @param string $name
     */
    public function setRepositoryConnectionMap($repository_name, $name)
    {
        $this->repository_connection_map[$repository_name] = $name;
    }

    /**
     * 指定されたリポジトリに対応するコネクションを取得
     *
     * @param string $repository_name
     * @return PDO
     */
    public function getConnectionForRepository($repository_name)
    {
        if (isset($this->repository_connection_map[$repository_name])) {
            $name = $this->repository_connection_map[$repository_name];
            $con = $this->getConnection($name);
        } else {
            $con = $this->getConnection();
        }

        return $con;
    }

    /**
     * リポジトリを取得
     *
     * 1度インタンスを作ったらそれ以降インスタンスを生成する必要はないように実装
     * 頻繁に扱うことになるので、短いメソッド名にした
     *
     * @param string $repository_name
     * @return DbRepository
     */
    public function get($repository_name)
    {
        // 指定された Repository 名が $repositories に入っていないときのみ
        // DbRepository インスタンスを生成する
        if (!isset($this->repositories[$repository_name])) {

            // ルールとして、名前の後に Repository をつけたものをクラス名にする
            $repository_class = $repository_name . 'Repository';

            $con = $this->getConnectionForRepository($repository_name);
            $repository = new $repository_class($con);
            $this->repositories[$repository_name] = $repository;
        }

        return $this->repositories[$repository_name];
    }

    /**
     * デストラクタ
     * リポジトリと接続を破棄する
     */
    public function __destruct()
    {
        foreach ($this->repositories as $repository) {
            unset($repository);
        }

        // Repository クラスを破棄してからでないと、
        // 参照が残っているため破棄できない
        foreach ($this->connections as $con) {
            unset($con);
        }
    }
}
