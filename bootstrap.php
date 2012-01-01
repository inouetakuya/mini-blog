<?php
/**
 * Created by JetBrains PhpStorm.
 * User: inouetakuya
 * Date: 12/01/01
 * Time: 17:45
 * To change this template use File | Settings | File Templates.
 */
require 'core/ClassLoader.php';

$loader = new ClassLoader();
$loader->registerDir(dirname(__FILE__).'/core');
$loader->registerDir(dirname(__FILE__).'/models');
$loader->register();
