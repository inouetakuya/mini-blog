<?php

/**
 * DbRepository.
 *
 * 実際にはデータベース上のテーブルごとにこのクラスを継承したクラスを
 * 作成し、そこにデータベースアクセス処理を記述する
 * 例: user テーブルがあれば UserRepository クラスを作成
 *
 * user テーブルヘレコードの新規作成を行う insert メソッドや
 * id というカラムを元にデータを取得する fetchById メソッドなどを
 * 必要に合わせて作成していくことを想定
 *
 * @author Katsuhiro Ogawa <fivestar@nequal.jp>
 */
abstract class DbRepository
{
    protected $con;

    /**
     * コンストラクタ
     *
     * @param PDO $con
     */
    public function __construct($con)
    {
        $this->setConnection($con);
    }

    /**
     * コネクションを設定
     *
     * @param PDO $con
     */
    public function setConnection($con)
    {
        $this->con = $con;
    }

    /**
     * クエリを実行
     *
     * @param string $sql
     * @param array $params
     * @return PDOStatement $stmt
     */
    public function execute($sql, $params = array())
    {
        // プレースホルダ部分は文字列として扱われるので、引用符で囲む必要はない
        // prepare メソッドの戻り値は PDOStatement クラスのインスタンス
        $stmt = $this->con->prepare($sql);

        // クエリをデータベースに発行する
        $stmt->execute($params);

        return $stmt;
    }

    /**
     * クエリを実行し、結果を1行取得
     *
     * クエリを実行し、返ってきた PDOStatement クラスの
     * インスタンスに対して fetch メソッドを実行
     *
     * @param string $sql
     * @param array $params
     * @return array
     */
    public function fetch($sql, $params = array())
    {
        // PDO::FETCH ASSOC という定数は、取得結果を連想配列で受け取るという指定
        return $this->execute($sql, $params)->fetch(PDO::FETCH_ASSOC);
    }


    /**
     * クエリを実行し、結果をすべて取得
     *
     * クエリを実行し、返ってきた PDOStatement クラスの
     * インスタンスに対して fetchAll メソッドを実行
     *
     * @param string $sql
     * @param array $params
     * @return array
     */
    public function fetchAll($sql, $params = array())
    {
        // PDO::FETCH ASSOC という定数は、取得結果を連想配列で受け取るという指定
        return $this->execute($sql, $params)->fetchAll(PDO::FETCH_ASSOC);
    }
}
