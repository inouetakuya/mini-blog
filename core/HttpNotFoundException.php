<?php

/**
 * HttpNotFoundException.
 *
 * メソッド内で処理を中断する場合は return を使えばいいのですが、
 * return で対応するのは少々ナンセンスです。 return を使う場合は
 * 戻り値を受け取ったタイミングで処理をしなければなりません
 * 404 エラー画面を表示する処理を様々な箇所で記述するか、
 * 処理を1箇所に書いたとしてもそこにたどり着くまで 404 を
 * 表す戻り値を返し続ける必要があります
 *
 * @author Katsuhiro Ogawa <fivestar@nequal.jp>
 */
class HttpNotFoundException extends Exception {};
