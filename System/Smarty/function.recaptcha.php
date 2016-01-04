<?php

function smarty_function_recaptcha($params, Smarty_Internal_Template $template) {
    return \TeamManager\Core::getInstance()->getRecaptchaHtml();
}
