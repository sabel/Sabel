<?php

/**
 * ������饹
 *
 */
class SabelConst
{
  /**
   * ����ȥ�������֤���ǥ��쥯�ȥ�̾��
   */
  const CONTROLLER_DIR     = 'controllers/';

  /**
   * ����ȥ���γ�ĥ��
   */
  const CONTROLLER_POSTFIX = '.php';

  /**
   * �⥸�塼������֤���ǥ��쥯�ȥ�
   */
  const MODULES_DIR        = 'app/modules/';

  /**
   * �ץ������ȶ��̤Υե�����ǥ��쥯�ȥ�
   */
  const COMMONS_DIR = 'app/commons/';

  /**
   * �ƥ�ץ졼�Ȥ����֤���ǥ��쥯�ȥ�
   */
  const TEMPLATE_DIR          = 'templates/';

  /**
   * �ƥ�ץ졼�ȥե�����γ�ĥ��
   */
  const TEMPLATE_POSTFIX   = '.tpl';

  /**
   * �ƥ�ץ졼�ȥե�����̾�ζ��ڤ�ʸ����
   */
  const TEMPLATE_NAME_SEPARATOR = '.';

  /**
   * �ǥե���ȤΥ⥸�塼��̾��
   */
  const DEFAULT_MODULE = 'Index';

  /**
   * �ǥե���ȤΥ���ȥ���̾��
   */
  const DEFAULT_CONTROLLER = 'index';

  /**
   * ����ȥ��餬��������ǥե���ȤΥ᥽�å�̾�Τ�
   * �᥽�åɻ��꤬��ά����Ƥ�����ǤΥ᥽�å�
   */
  const DEFAULT_METHOD     = 'index';

  /**
   * SabelException�Υ��ե������̾��
   *
   */
  const EXCEPTION_LOG_FILE_NAME = 'sabelException.log';
}

?>
