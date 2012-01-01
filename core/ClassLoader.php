<?php
/**
 * パーフェクト PHP P204
 *
 * オートロードを設定すると、クラスを呼び出した際にそのクラスが
 * PHP 上に読み込まれていない場合、自動的にファイルの読み込みを
 * 行うことができるようになります
 *
 * オートローダを行うクラスに実装する必要がある機能:
 * 1. PHP にオートローダクラスを登録する
 * 2. オートロードが実行された際にクラスファイルを読み込む
 *
 * オートロード対象となるクラスのルール:
 * クラスは「クラス名.php」というファイル名で保存する
 * クラスは core ディレクトリ及び model ディレクトリに配置する
 *
 * User: inouetakuya
 * Date: 11/12/18
 * Time: 1:11
 * To change this template use File | Settings | File Templates.
 */
class ClassLoader
{
    protected $dirs;

    /**
     * PHP にオートローダクラスを登録する
     */
    public function register()
    {
        spl_autoload_register(array($this, 'loadClass'));
    }

    /**
     * クラスを配置するディレクトリを登録する
     *
     * @param $dir クラスを配置するディレクトリ
     */
    public function registerDir($dir)
    {
        $this->dirs[] = $dir;
    }

    /**
     * オートロード時に PHP から自動的に呼び出され、クラスファイルの読み込みを行う
     *
     * @param $class
     */
    public function loadClass($class)
    {
        foreach ($this->dirs as $dir) {
            $file = $dir . '/' . $class . '.php';
            if (is_readable($file)) {
                require $file;

                return;
            }
        }
    }
}