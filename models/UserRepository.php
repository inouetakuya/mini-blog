<?php

/**
 * UserRepository.
 *
 * ビジネスロジックは極カモデルに集約させるようにするとアプリケーションの
 * メンテナンスがしやすくなる
 *
 * ユーザ登録時にユーザ ID が重複しないように既に使用されている
 * ユーザ ID かどうかチェックする
 *
 * ユーザ登録が完了した後にユーザ ID からレコードを取得する機能も実装
 *
 * レコードを取得する場合は fetch で始まるメソッド名にする
 * その際、単一レコードの取得は fetch、複数レコードの取得は fetchAll ではじめる
 * 特定のカラムを指定して取得する場合は fetchById のように By カラム名をメソッド名につける
 *
 * ユーザの入力値やセッションに格納されている情報を Repository クラスから
 * 直接取得しないようにする。そういった情報は Controller クラスでのみ取得
 *
 * @author Katsuhiro Ogawa <fivestar@nequal.jp>
 */
class UserRepository extends DbRepository
{
    public function insert($user_name, $password)
    {
        $password = $this->hashPassword($password);
        $now = new DateTime();

        $sql = "
            INSERT INTO user(user_name, password, created_at)
                VALUES(:user_name, :password, :created_at)
        ";

        $stmt = $this->execute($sql, array(
            ':user_name'  => $user_name,
            ':password'   => $password,
            ':created_at' => $now->format('Y-m-d H:i:s'),
        ));
    }

    public function hashPassword($password)
    {
        return sha1($password . 'SecretKey');
    }

    public function fetchByUserName($user_name)
    {
        $sql = "SELECT * FROM user WHERE user_name = :user_name";

        return $this->fetch($sql, array(':user_name' => $user_name));
    }

    public function isUniqueUserName($user_name)
    {
        $sql = "SELECT COUNT(id) as count FROM user WHERE user_name = :user_name";

        $row = $this->fetch($sql, array(':user_name' => $user_name));
        if ($row['count'] === '0') { // SQL の実行結果は文字列型なので、文字列の '0' と比較
            return true;
        }

        return false;
    }

    public function fetchAllFollowingByUserId($user_id)
    {
        $sql = "
            SELECT u.*
            FROM user u
                LEFT JOIN following f ON f.following_id = u.id
            WHERE f.user_id = :user_id
        ";

        return $this->fetchAll($sql, array(':user_id' => $user_id));
    }
}
