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

class plgCaptchaQlcaptchaSimplex
{
    public $solution;
    /**
     * path and filename for the catpcha font
     * @var string
     * @access public
     */
    public $strFont;
    /**
     * path where the rendered captcha image should be safed
     * @var int
     * @access public
     */
    public $destination;
    /**
     * the width of the captcha image
     * @var int
     * @access public
     */
    public $intIMGWidth = 140;
    /**
     * the height of the captcha image
     * @var int
     * @access public
     */
    public $intIMGHeight = 80;
    /**
     * an array with rgb colors backgroundcolors of the captcha image
     * @var array
     * @access public
     */
    public $arrBGColor = [255, 255, 255];
    /**
     * an array with rgb colors fontcolors of the captcha letters
     * @var array
     * @access public
     */
    public $arrTextColor = [24, 24, 24];
    /**
     * the number of letters
     * @var int
     * @access public
     */
    public $intTextLenght = 4;
    /**
     * the fontsize of the captcha letters
     * @var int
     * @access public
     */
    public $intFontSize = 26;
    /**
     * the angel of the captcha letters
     * @var int
     * @access public
     */
    public $intFontAngel = 5;
    /**
     * the horizontal start position of the captcha letters
     * @var int
     * @access public
     */
    public $intFontStartX = 20;
    /**
     * the vertical start position of the captcha letters
     * @var int
     * @access public
     */
    public $intFontStartY = 50;

    /**
     * wants to have file path and folder path
     * @param array $arrBG with rgb color information for background
     * @param array $arrTxt with rgb color information for text
     * @param string $strFont path of font-file
     * @return bool ture on success, false on failure
     */
    function __construct($strFont, $destination, $transparent = 0)
    {
        $this->strFont = $strFont;
        $this->strCaptchaSaveFile = $destination;
        $this->transparent = $transparent;
    }

    /**
     * generates a captacha
     * @return string the rendered text
     */
    function generateCaptcha()
    {
        $this->handleImage = imagecreate($this->intIMGWidth, $this->intIMGHeight);
        $colBackground = imagecolorallocate($this->handleImage, $this->arrBGColor[0], $this->arrBGColor[1], $this->arrBGColor[2]);
        $text_color = imagecolorallocate($this->handleImage, $this->arrTextColor[0], $this->arrTextColor[1], $this->arrTextColor[2]);
        $this->text = $this->randomText();
        ImageTTFText($this->handleImage, $this->intFontSize, $this->intFontAngel, $this->intFontStartX, $this->intFontStartY, $text_color, $this->strFont, (string)$this->text);
        if (1 == $this->transparent) imagecolortransparent($this->handleImage, $colBackground);
        imagegif($this->handleImage, $this->strCaptchaSaveFile);
        chmod($this->strCaptchaSaveFile, 0755);
        //echo '#'.$this->strCaptchaSaveFile.' '.$this->text.'=>'.$this->solution.' #';die;
        return $this->solution;
    }

    /**
     * generates a random text for the captcha image
     * @param int the wanted lenght of the text
     * @return string
     */
    function randomText()
    {
        $chars = 'bcdefghkmnopqrstuvwxyz2345678';
        $string = '';
        mt_srand((double)microtime() * 1000000);
        for ($i = 0; $i < $this->intTextLenght; $i++) {
            $string .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        $this->solution = $string;
        return $string;
    }
}
