<?php namespace Teepluss\Ldap\Facades;

use Illuminate\Support\Facades\Facade;

class iLdap extends Facade {

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'ldap'; }

}