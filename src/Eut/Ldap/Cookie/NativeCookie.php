<?php namespace Eut\Ldap\Cookie;

use Illuminate\Foundation\Application;

class NativeCookie {

    /**
     * Application Foundation
     *
     * @var Application
     */
    protected $app;


    protected $request;

    protected $encrypter;

    protected $path;

    protected $domain;

    /**
     * Create a new cookie manager instance.
     *
     * @param  \Symfony\Component\HttpFoundation\Request  $request
     * @param  \Illuminate\Encryption\Encrypter  $encrypter
     * @return void
     */
    public function __construct(Application $app)
    {
        $this->app = $app;

        $this->request = $this->app['request'];

        $this->encrypter = $this->app['encrypter'];

        return $this;
    }

    public function setDefaultPathAndDomain($path, $domain)
    {
        $this->path = $path;

        $this->domain = $domain;

        return $this;
    }

    /**
     * Create a new cookie
     *
     * @param  string  $name
     * @param  string  $value
     * @param  int     $minutes
     * @param  string  $path
     * @param  string  $domain
     * @param  bool    $secure
     * @param  bool    $httpOnly
     * @return void
     */
    public function make($name, $value, $minutes = 0, $path = null, $domain = null, $secure = false, $httpOnly = true)
    {
        $path = $path ?: $this->path;

        $domian = $domain ?: $this->domain;

        $seconds = $minutes * 60;

        $value = $this->encrypter->encrypt($value);

        return setcookie($name, $value, time() + $seconds, $path, $domain, $secure, $httpOnly);
    }

    public function forever($name, $value, $path = null, $domain = null, $secure = false, $httpOnly = true)
    {
        return $this->make($name, $value, 2628000, $path, $domain, $secure, $httpOnly);
    }

    /**
     * Expire the given cookie.
     *
     * @param  string  $name
     * @return void
     */
    public function forget($name)
    {
        return $this->make($name, null, -2628000);
    }

    public function get($cookie)
    {
        $value =  isset($_COOKIE[$cookie]) ? $_COOKIE[$cookie] : null;

        if ( ! $value) return;

        return $this->encrypter->decrypt($value);
    }

}