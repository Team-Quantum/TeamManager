<?php
/**
 * Created by PhpStorm.
 * User: .PolluX
 * Date: 1/4/2016
 * Time: 4:33 PM
 */

namespace TeamManager;

use Smarty;

class Controller extends BasePage {

    /**
     * Automatic called before smarty display is called
     * @param $core Core
     * @param $smarty Smarty
     * @return void
     */
    public function preRender($core, $smarty)
    {
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

    /**
     * 404 page not found for controller
     */
    public function _404()
    {
        $this->core->displayNotFound();
    }
}