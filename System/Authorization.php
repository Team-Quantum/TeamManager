<?php
/**
 * Created by PhpStorm.
 * User: .PolluX
 * Date: 1/4/2016
 * Time: 12:59 AM
 */

namespace TeamManager;


trait Authorization
{
    /**
     * Authorize current user. If it fails it will redirect him to the homepage
     *
     * @param $core Core
     * @return bool
     */
    public function authorize($core)
    {
        $rights = false;

        if($core->getAccount() != null) {
            $neededPrivilege = property_exists($this, 'neededPrivileges') ? $this->neededPrivileges : null;

            if($neededPrivilege == null) {
                $rights = true;
            } else {
                $rights = $core->getUserManager()->hasPrivilege($neededPrivilege);
            }
        }
        return $rights;
    }
}