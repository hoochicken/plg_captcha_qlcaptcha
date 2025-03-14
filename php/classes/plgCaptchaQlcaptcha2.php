<?php
/**
 * @package     plg_captcha_qlcaptcha
 * @subpackage  Captcha
 * @copyright   Copyright (C) 2025 ql.de. All rights reserved.
 * @author      Mareike Riegel
 * @email       info@ql.de
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

class plgCaptchaQlcaptcha2
{
    /**
     * wants to have file path and folder path
     * @param $obj
     * @param $tmp
     * @return bool ture on success, false on failure
     */
    //function __construct($strFont,$strCaptchaSaveFile)

    public function initiateCaptcha($obj, $tmp)
    {
        $this->_name = $obj->getExtName();
        $this->params = $obj->params;
        $this->tmp = $tmp;
        $this->destination = $this->getFilename($tmp, uniqid());
        $this->captcha = $this->destination . '?' . rand(0, 10000);
        $this->type = $this->params->get('captcha', 1);
        $this->fontPath = $this->params->get('fontPath', 'plugins/captcha/' . $this->_name . '/fonts/LS-Bold.otf');
        if (1 == $this->type) {
            $this->initiateCaptchaSimplex();
        }
        if (2 == $this->type) {
            $this->initiateCaptchaCalculator();
        }
        $this->runCaptchaSettings();
        $this->obj_captcha->captcha = $this->captcha;
        $this->key = $this->getKey(false, true);
        $this->obj_captcha->generateCaptcha();
        $this->setSession($this->key);
    }

    private function initiateCaptchaSimplex()
    {
        require_once(JPATH_ROOT . '/plugins/captcha/' . $this->_name . '/php/classes/plgCaptchaQlcaptchaSimplex.php');
        $this->obj_captcha = new plgCaptchaQlcaptchaSimplex($this->fontPath, $this->destination, $this->params->get('captcha_arrBGColorTransparent'));
    }

    private function initiateCaptchaCalculator()
    {
        require_once(JPATH_ROOT . '/plugins/captcha/qlcaptcha/php/classes/plgCaptchaQlcaptchaSimplex.php');
        require_once(JPATH_ROOT . '/plugins/captcha/qlcaptcha/php/classes/plgCaptchaQlcaptchaCalculator.php');
        $this->obj_captcha = new plgCaptchaQlcaptchaCalculator($this->fontPath, $this->destination, $this->params->get('captcha_arrBGColorTransparent'));
    }

    function runCaptchaSettings()
    {
        $array = ['intTextLenght', 'intFontSize', 'intFontStartX', 'intFontStartY', 'intIMGWidth', 'intIMGHeight', 'arrTextColor', 'arrBGColor', 'intFontAngel'];
        foreach ($array as $v) {
            if ('arrTextColor' != $v and 'arrBGColor' != $v) {
                $this->obj_captcha->$v = $this->params->get('captcha_' . $v);
            } else {
                $this->obj_captcha->$v = $this->hexcolor($this->params->get('captcha_' . $v));
            }
        }
    }

    public function hexcolor($hex)
    {
        $hex = preg_replace('/#/', '', $hex);
        $color = [];
        if (3 == strlen($hex)) {
            $color[] = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
            $color[] = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
            $color[] = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
        } else if (6 == strlen($hex)) {
            $color[] = hexdec(substr($hex, 0, 2));
            $color[] = hexdec(substr($hex, 2, 2));
            $color[] = hexdec(substr($hex, 4, 2));
        }
        return $color;
    }

    /**
     * checks captcha
     * @return string the rendered text
     */
    function getFilename($tmp, $id)
    {
        return $tmp . '/' . session_id() . '_' . $id . '_' . date('ymdhis') . '.gif';
    }

    /**
     * checks captcha
     * @return string the rendered text
     */
    static function checkCaptcha($strCaptcha, $key)
    {
        $session = JFactory::getSession();
        $arrSession = $session->get('plgCaptchaQlcaptcha');
        if (isset($strCaptcha) && is_array($arrSession) && isset($arrSession['code'][$key]) && isset($arrSession['code'][$key]) && $strCaptcha == $arrSession['code'][$key]) {
            return true;
        }
        return false;
    }

    /**
     * start session and initiate session variables
     * @return string the rendered text
     */
    function setSession($key)
    {
        $session = JFactory::getSession();
        $arrSession = $session->get('plgCaptchaQlcaptcha');
        if (!is_array($arrSession)) {
            $arrSession = [];
        }
        if (!isset($arrSession['filename']) || !is_array($arrSession['filename'])) {
            $arrSession['filename'] = [];
        }
        if (!isset($arrSession['text']) || !is_array($arrSession['filename'])) {
            $arrSession['text'] = [];
        }
        if (!isset($arrSession['code']) || !is_array($arrSession['code'])) {
            $arrSession['code'] = [];
        }
        if (!isset($arrSession['code'][$key])) {
            $arrSession['code'][$key] = [];
        }
        $arrSession['text'][$key] = $this->obj_captcha->text;
        $arrSession['filename'][$key] = $this->destination;
        $arrSession['code'][$key] = $this->obj_captcha->solution;
        $session->set('plgCaptchaQlcaptcha', $arrSession);
    }

    /**
     * Method to check if folder exists and generates it eventually
     */
    public function checkTmpQlcaptcha($folder)
    {
        if (!is_dir($folder)) {
            mkdir($folder);
            chmod($folder, 0755);
        }
        if (!is_file($folder . '/index.html')) {
            touch($folder . '/index.html');
        }
        $this->checkTmpQlcaptchaFiles($folder);
    }

    /**
     * Method to check for old files and to remove them
     */
    public function checkTmpQlcaptchaFiles($folder)
    {
        $handle = opendir($folder);
        while ($file = readdir($handle)) {
            if ('.' === $file || '..' === $file || 'index.html' === $file) {
                continue;
            }
            $arr = preg_split('?_?', $file);
            $dateFile = (int)substr(array_pop($arr), 0, 6);
            if ($dateFile + 1 < date('ymd') && file_exists($folder . '/' . $file)) {
                unlink($folder . '/' . $file);
            }
        }
        closedir($handle);
    }

    /**
     * method to generate unique key; check session, if key already exists
     * @param bool $key
     * @return bool|int
     */
    function getKey($key = false, $ignoreKey = false)
    {
        if (false == $key) {
            $key = rand(1, 10000);
        }
        if (isset($_SESSION['plgCaptchaQlcaptcha']) && isset($_SESSION['plgCaptchaQlcaptcha']['code']) && isset($_SESSION['plgCaptchaQlcaptcha']['code'][$key])) {
            $key = $this->getKey($key);
        }
        return $key;
    }

}
