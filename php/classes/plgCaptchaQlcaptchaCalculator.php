<?php
/**
 * @package     plg_captcha_qlcaptcha
 * @subpackage  Captcha
 * @copyright   Copyright (C) 2022 ql.de. All rights reserved.
 * @author      Mareike Riegel
 * @email       mareike.riegel@ql.de
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

class plgCaptchaQlcaptchaCalculator extends plgCaptchaQlcaptchaSimplex
{
    /**
     * generates a random text for the captcha image
     * @param int the wanted lenght of the text
     * @return string
     */
    function randomText()
    {
        $this->level = 1;
        $first = rand(5, 20);
        $second = rand(5, 10);
        $type = array_rand([0, 1]);
        if (0 == $type && $first > $second && 0 == $first % $second) {
            $string = $first . ' : ' . $second;
            $this->solution = $first / $second;
        } elseif (0 == $type && $first > $second) {
            $this->solution = $first - $second;
            $string = $first . ' - ' . $second;
        } elseif (1 == $type && $first <= 10 && $second <= 10) {
            $string = $first . ' x ' . $second;
            $this->solution = $first * $second;
        } else {
            $string = $first . ' + ' . $second;
            $this->solution = $first + $second;
        }
        return $string;
    }
}
