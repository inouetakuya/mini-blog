<?php
 
/**
 * View.
 *
 * ビューファイルの読み込みは require を用いれば実行可能だが、
 * その際、読み込んだファイル内で出力が行われていると、読み込んだ時点で
 * 出力が行われてしまう。出力情報を文字列として読み込み、
 * レスポンスに設定する必要がある
 *
 * そこでアウトプットバッファリングという仕組みを用いて、出力を文字列として取得する
 *
 * require を用いてファイルを読み込むと、require を実行した側で
 * アクセス可能な変数に対し、読み込まれた側のファイルでもアクセスすることができる
 *
 * @author Katsuhiro Ogawa <fivestar@nequal.jp>
 */
class View
{
    protected $base_dir;
    protected $defaults;
    protected $layout_variables = array();

    /**
     * コンストラクタ
     *
     * @param string $base_dir
     * @param array $defaults
     */
    public function __construct($base_dir, $defaults = array())
    {
        $this->base_dir = $base_dir;
        $this->defaults = $defaults;
    }

    /**
     * レイアウトに渡す変数を指定
     *
     * @param string $name
     * @param mixed $value
     */
    public function setLayoutVar($name, $value)
    {
        $this->layout_variables[$name] = $value;
    }

    /**
     * ビューファイルをレンダリング
     *
     * @param string $_path
     * @param array $_variables
     * @param mixed $_layout
     * @return string
     */
    public function render($_path, $_variables = array(), $_layout = false)
    {
        $_file = $this->base_dir . '/' . $_path . '.php';

        // extract - 連想配列のキーを変数名に、連想配列の値を変数の値として展開
        extract(array_merge($this->defaults, $_variables));

        // アウトプットバッファリングを開始
        // バッファリング中に echo で出力された文字列は
        // 画面には直接表示されず、内部のバッファにため込まれる
        ob_start();

        // バッファの自動フラッシュを無効にする
        // 自動フラッシュが設定されていると、バッファの容量を
        // 超えた際などにバッファの内容が自動的に出力される
        ob_implicit_flush(0);

        // アウトプットバッファリングを開始した状態でビューファイルを
        // 読み込むとバッファにビューファイルの内容が格納される
        require $_file;

        // バッファに格納された文字列を取得
        // 同時にバッファのクリアも行う
        $content = ob_get_clean();

        if ($_layout) {
            $content = $this->render($_layout,
                array_merge($this->layout_variables, array(
                    '_content' => $content,
                )
            ));
        }

        return $content;
    }

    /**
     * 指定された値をHTMLエスケープする
     *
     * @param string $string
     * @return string
     */
    public function escape($string)
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
}
