<?php

/**
 * MiniBlogApplication.
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
