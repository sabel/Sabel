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
   * テンプレートを配置するディレクトリ
   */
  const TEMPLATE_DIR          = 'templates/';

  /**
   * テンプレートファイルの拡張子
   */
  const TEMPLATE_POSTFIX   = '.tpl';

  const TEMPLATE_NAME_SEPARATOR = '.';

  /**
   * コントローラが実装するデフォルトのメソッド名称
   */
  const DEFAULT_METHOD     = 'defaults';
}

?>