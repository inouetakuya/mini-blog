<?php

/**
 * Created by JetBrains PhpStorm.
 * User: inouetakuya
 * Date: 12/01/02
 * Time: 5:30
 * To change this template use File | Settings | File Templates.
 */
class Router
{
    protected $routes;

    public function __construct($definitions)
    {
        $this->routes = $this->compileRoutes($definitions);
    }

    /**
     * ルーティング定義配列のそれぞれのキーに含まれる動的パラメータを
     * 正規表現でキャプチャできる形式に変換する
     *
     * @param $definitions ルーティング定義配列
     * @return array 正規表現でキャプチャできる形式に変換された動的パラメータ
     */
    public function compileRoutes($definitions)
    {
        $routes = array();

        // 例 $url: '/user/:id',
        // $params: array('controller' => 'user', 'action' => 'show')
        foreach ($definitions as $url => $params) {
            $tokens = explode('/', ltrim($url, '/'));
            foreach ($tokens as $i => $token) {

                // : で始まる文字列があったとき、正規表現の形式に変換する
                if (0 === strpos($token, ':')) {
                    $name = substr($token, 1);
                    $token = '(?P<' . $name . '>[^/]+)';
                }
                $tokens[$i] = $token;
            }

            // implode — 配列要素を文字列により連結する
            $pattern = '/' . implode('/', $tokens);

            // 例: $routes['/user/(?P<id>[^/]+)'] = array('controller' => 'user', 'action' => 'show')
            $routes[$pattern] = $params;
        }

        return $routes;
    }

    /**
     * 変換済みのルーティング定義配列と PATH_INFO のマッチングを行う
     *
     * @param $path_info
     * @return bool
     *
     * 例: $routes['/user/(?P<id>[^/]+)'] = array('controller' => 'user', 'action' => 'show')
     */
    public function resolve($path_info)
    {
        // 先頭がスラッシュでないとき、先頭にスラッシュを付与する
        if ('/' !== substr($path_info, 0, 1)) {
            $path_info = '/' . $path_info;
        }

        foreach ($this->routes as $pattern => $params) {
            if (preg_match('#^' . $pattern . '$#', $path_info, $matches)) {

                // array_merge - 配列を連結する
                // array('controller' => 'user', 'action' => 'show') に
                // array('id' => '1') が加わる
                $params = array_merge($params, $matches);

                return $params;
            }
        }

        return false;
    }
}
