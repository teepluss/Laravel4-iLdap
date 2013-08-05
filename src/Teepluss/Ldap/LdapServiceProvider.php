<?php namespace Teepluss\Ldap;

use Illuminate\Support\ServiceProvider;

class LdapServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = true;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('teepluss/ldap');

		$this->registerLdapEvents();
	}

	/**
	 * Register the events needed for authentication.
	 *
	 * @return void
	 */
	protected function registerLdapEvents()
	{
		$app = $this->app;

		$app->after(function($request, $response) use ($app)
		{
			if (isset($app['ldap.loaded']))
			{
				foreach ($app['ldap']->getQueuedCookies() as $cookie)
				{
					$response->headers->setCookie($cookie);
				}
			}
		});
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app['ldap'] = $this->app->share(function($app)
		{
			$app['ldap.loaded'] = true;

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
		return array('ldap');
	}

}