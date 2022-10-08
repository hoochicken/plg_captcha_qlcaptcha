<?php
/**
 * @package     plg_captcha_qlcaptcha
 * @subpackage  Captcha
 * @copyright   Copyright (C) 2016 ql.de. All rights reserved.
 * @author      Mareike Riegel
 * @email       mareike.riegel@ql.de
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Qlcaptcha Plugin.
 */
class PlgCaptchaQlcaptcha extends JPlugin
{
    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since  3.1
     */
    protected $autoloadLanguage = true;

    /**
     * constructor
     *setting language
     */
    public function __construct(&$subject, $config)
    {
        parent::__construct($subject, $config);
        $this->loadLanguage();
    }

    /**
     * Initialise the captcha
     *
     * @param string $id The id of the field.
     *
     * @return  Boolean    True on success, false otherwise
     *
     * @throws  Exception
     *
     * @since  2.5
     */
    public function onInit($id = 'qlcaptcha')
    {
        require_once(JPATH_ROOT . '/plugins/captcha/qlcaptcha/php/classes/plgCaptchaQlcaptcha2.php');
        $tmp = 'tmp/' . $this->get('_name');
        $this->obj_captcha = new plgCaptchaQlcaptcha2();
        $this->obj_captcha->checkTmpQlcaptcha($tmp);
        $objPlg = clone $this;
        $objPlg->extName = $this->get('_name');
        $this->obj_captcha->initiateCaptcha($objPlg, $tmp);
    }

    /**
     * Gets the challenge HTML
     *
     * @param string $name The name of the field. Not Used.
     * @param string $id The id of the field.
     * @param string $class The class of the field. This should be passed as
     *                          e.g. 'class="required"'.
     *
     * @return  string  The HTML to be embedded in the form.
     *
     * @since  2.5
     */
    public function onDisplay($name = null, $id = 'dynamic_qlcaptcha_1', $class = '')
    {
        $html = array();
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
     * @param string $code Answer provided by user. Not needed for the Qlcaptcha implementation
     * @return  True if the answer is correct, false otherwise
     * @since  2.5
     */
    public function onCheckAnswer($code = null)
    {
        $input = JFactory::getApplication()->input;
        $strCaptcha = $input->getString('qlcaptcha');
        $key = $input->getString('qlcaptchakey');
        require_once(JPATH_ROOT . '/plugins/captcha/qlcaptcha/php/classes/plgCaptchaQlcaptcha2.php');
        $validated = plgCaptchaQlcaptcha2::checkCaptcha($strCaptcha, $key, true);
        if (false == $validated) $this->_subject->setError(JText::_('PLG_CAPTCHA_QLCAPTCHA_MSG_CAPTCHAFAILED'));
        return $validated;
    }
}