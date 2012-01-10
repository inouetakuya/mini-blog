<?php

/**
 * MiniBlogApplication.
 *
 * P271 アクションの作成手順
 * 1. データベースアクセス処理を Repository クラスに定義
 * 2. ルーティングを MiniBlogApplicalon クラスに定義
 * 3. コントローラクラスを定義
 * 4. コントローラクラスにアクションを定義
 * 5. アクションのビューファイルを記述
 *
 * @author Katsuhiro Ogawa <fivestar@nequal.jp>
 */
class MiniBlogApplication extends Application
{
    protected $login_action = array('account', 'signin');

    public function getRootDir()
    {
        return dirname(__FILE__);
    }

    protected function registerRoutes()
    {
        return array(
            '/account'
                => array('controller' => 'account', 'action' => 'index'),
            '/account/:action'
                => array('controller' => 'account'),
        );
    }

    protected function configure()
    {
        $this->db_manager->connect('master', array(
            'dsn'      => 'mysql:dbname=mini_blog;host=localhost',
            'user'     => 'mini_blog_admin',
            'password' => 'mini_blog_admin',
        ));
    }
}
