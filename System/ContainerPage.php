<?php
/**
 * Created by PhpStorm.
 * User: .PolluX
 * Date: 1/3/2016
 * Time: 8:17 PM
 */

namespace TeamManager;


class ContainerPage extends BasePage {

    /**
     * Automatic called before smarty display is called
     * @param $core Core
     * @param $smarty \Smarty
     * @return void
     */
    public function preRender($core, $smarty)
    {
    }

    /**
     * Automatic called after preRender and before smarty display is called
     * @param $core Core
     * @param $smarty \Smarty
     * @return string template file for page content
     */
    public function getTemplate($core, $smarty)
    {
    }

    /**
     * Automatic called after smarty display is called. Example usage: clean up cache
     * @param $core Core
     * @param $smarty \Smarty
     * @return void
     */
    public function postRender($core, $smarty)
    {
    }
}