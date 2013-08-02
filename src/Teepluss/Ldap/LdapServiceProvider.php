<?php namespace Teepluss\Ldap;

use Illuminate\Support\ServiceProvider;

class LdapServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	public function boot()
	{
		$this->package('teepluss/ldap');
	}

	public function register()
	{
		$this->registerCookie();
		$this->registerLdap();
	}

	protected function registerCookie()
	{
		$this->app['cookie'] = $this->app->share(function($app)
        {
            $cookies = new Cookie\NativeCookie($app);

            $config = $app['config']['session'];

            return $cookies->setDefaultPathAndDomain($config['path'], $config['domain']);
        });
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	protected function registerLdap()
	{
		$this->app['ldap'] = $this->app->share(function($app)
		{
			$connection = $app['config']->get('ldap::connections');

			$ldap = new Ldap($app['config'], $app['session'], $app['cookie']);

			$ldap->addConnections($connection)->connect('default');

			return $ldap;
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('cookie', 'ldap');
	}

}