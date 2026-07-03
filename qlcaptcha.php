<?php
/**
 * @package     plg_captcha_qlcaptcha
 * @subpackage  Captcha
 * @copyright   Copyright (C) 2025 ql.de. All rights reserved.
 * @author      Mareike Riegel
 * @email       info@ql.de
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;

defined('_JEXEC') or die;

/**
 * Qlcaptcha Plugin.
 */
class PlgCaptchaQlcaptcha extends CMSPlugin
{
    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since  3.1
     */
    protected $autoloadLanguage = true;
    protected plgCaptchaQlcaptcha2 $obj_captcha;
    protected array $messages = [];
    private $extName;

    /**
     * constructor
     *setting language
     */
    public function __construct(&$subject, $config)
    {
        parent::__construct($subject, $config);
        $this->loadLanguage();
    }

    public function onInit(string $id = 'qlcaptcha'): void
    {
        try {
            require_once(JPATH_ROOT . '/plugins/captcha/qlcaptcha/php/classes/plgCaptchaQlcaptcha2.php');
            if (!function_exists('imagecreate')) {
                throw new Exception('qlcaptcha requires the library `gd` on server. Please install. Then it will work:-)');
            }
            $name = method_exists($this, 'get') ? $this->get('_name') : $this->_name;
            $tmp = 'tmp/' . $name; // $this->get('_name');
            $this->obj_captcha = new plgCaptchaQlcaptcha2();
            $this->obj_captcha->checkTmpQlcaptcha($tmp);
            $objPlg = clone $this;
            $objPlg->setExtName($name);
            $this->obj_captcha->initiateCaptcha($objPlg, $tmp);
        } catch (Exception $e) {
            $this->setMessage($e->getMessage());
        }
    }

    public function setExtName($value): void
    {
        $this->extName = $value;
    }

    public function getExtName()
    {
        return $this->extName;
    }

    public function onDisplay($name = null, $id = 'dynamic_qlcaptcha_1', $class = ''): string
    {
        $html = [];
        if (0 < count($this->messages)) {
            $html[] = '<div class="alert alert-info">' . implode('<br />', $this->messages) . '</div>';
        }
        if (is_null($this->obj_captcha)) {
            return implode("\n", $html);
        }

        $html[] = '<div class="image">';
        $html[] = '<img src="' . $this->obj_captcha->captcha . '"/>';
        $html[] = '</div>';
        $html[] = '<div class="input">';
        $html[] = '<input ';
        if (1 == $this->params->get('captchaLabelswithin')) $html[] = 'placeholder="' . JText::_($this->params->get('captchadesc')) . '" ';
        $html[] = 'type="text" autocorrect="off" autocapitalize="off" name="qlcaptcha" id="qlcaptcha" value="" />';
        $html[] = '<input type="hidden" name="qlcaptchakey" value="' . $this->obj_captcha->key . '" />';
        $html[] = '</div>';
        $html[] = '<div class="description">';
        if (1 != $this->params->get('captchaLabelswithin', 0)) $html[] = '<label for="captcha"><span>' . JText::_($this->params->get('captchadesc')) . '</span></label>';
        $html[] = '</div>';
        return implode("\n", $html);
    }

    /**
     * Calls an HTTP POST function to verify if the user's guess was correct
     * @param string|null $code Answer provided by user. Not needed for the Qlcaptcha implementation
     * @return  True if the answer is correct, false otherwise
     * @throws Exception
     * @since  2.5
     */
    public function onCheckAnswer(?string $code = null)
    {
        $input = Factory::getApplication()->input;
        $strCaptcha = $input->getString('qlcaptcha');
        $key = $input->getString('qlcaptchakey');

        require_once(JPATH_ROOT . '/plugins/captcha/qlcaptcha/php/classes/plgCaptchaQlcaptcha2.php');
        $validated = plgCaptchaQlcaptcha2::checkCaptcha($strCaptcha, $key, true);
        if (!$validated) {
            Factory::getApplication()->enqueueMessage(Text::_('PLG_CAPTCHA_QLCAPTCHA_MSG_CAPTCHAFAILED'));
        }
        return $validated;
    }
    
    private function setMessage($message)
    {
        $this->messages[] = $message;
    }
}
