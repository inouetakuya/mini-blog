<?php
/**
 * Created by JetBrains PhpStorm.
 * User: inouetakuya
 * Date: 11/12/18
 * Time: 1:11
 * To change this template use File | Settings | File Templates.
 */
class ClassLoader
{
    protected $dirs;

    public function register()
    {
        spl_autoload_register(array($this, 'loacClass'));
    }

    public function registerDir($dir)
    {
        $this->dirs[] = $dir;
    }

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