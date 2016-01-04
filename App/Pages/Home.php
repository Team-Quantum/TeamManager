<?php
/**
 * Created by PhpStorm.
 * User: .PolluX
 * Date: 1/4/2016
 * Time: 4:45 PM
 */

namespace App\Pages;

use Smarty;
use TeamManager\BasePage;
use TeamManager\Core;


class Home extends BasePage
{

    /**
     * Automatic called before smarty display is called
     * @param $core Core
     * @param $smarty Smarty
     * @return void
     */
    public function preRender($core, $smarty)
    {
        $this->smarty->assign('site_template', 'home.tpl');
    }

    /**
     * Automatic called after preRender and before smarty display is called
     * @param $core Core
     * @param $smarty Smarty
     * @return string template file for page content
     */
    public function getTemplate($core, $smarty)
    {
    }

    /**
     * Automatic called after smarty display is called. Example usage: clean up cache
     * @param $core Core
     * @param $smarty Smarty
     * @return void
     */
    public function postRender($core, $smarty)
    {
    }
}