<?php

/**
 * 定数クラス
 *
 */
class SabelConst
{
  /**
   * コントローラを配置するディレクトリ名称
   */
  const CONTROLLER_DIR     = 'controllers/';

  /**
   * コントローラの拡張子
   */
  const CONTROLLER_POSTFIX = '.php';

  /**
   * モジュールを配置するディレクトリ
   */
  const MODULES_DIR        = 'app/modules/';

  /**
   * プロジェクト共通のファイルディレクトリ
   */
  const COMMONS_DIR = 'app/commons/';

  /**
   * テンプレートを配置するディレクトリ
   */
  const TEMPLATE_DIR          = 'templates/';

  /**
   * テンプレートファイルの拡張子
   */
  const TEMPLATE_POSTFIX   = '.tpl';

  /**
   * テンプレートファイル名の区切り文字列
   */
  const TEMPLATE_NAME_SEPARATOR = '.';

  /**
   * デフォルトのモジュール名称
   */
  const DEFAULT_MODULE = 'Index';

  /**
   * デフォルトのコントローラ名称
   */
  const DEFAULT_CONTROLLER = 'index';

  /**
   * コントローラが実装するデフォルトのメソッド名称と
   * メソッド指定が省略されている場合でのメソッド
   */
  const DEFAULT_METHOD     = 'index';

  /**
   * SabelExceptionのログファイルの名前
   *
   */
  const EXCEPTION_LOG_FILE_NAME = 'sabelException.log';
}

?>
