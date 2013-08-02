## Ldap authentication

This is my internal project, not yet complete.

### Installation

To get the lastest version of Theme simply require it in your `composer.json` file.

~~~
"repositories" : [
    {
        "type": "vcs",
        "url": "https://github.com/teepluss/Laravel4-iLdap"
    }
],
"require": {
    "teepluss/ldap": "dev-master"
}
~~~

You'll then need to run `composer install` to download it and have the autoloader updated.

Once Theme is installed you need to register the service provider with the application. Open up `app/config/app.php` and find the `providers` key.

~~~
'providers' => array(

    'Teepluss\Ldap\LdapServiceProvider'

)
~~~

Theme also ships with a facade which provides the static syntax for creating collections. You can register the facade in the `aliases` key of your `app/config/app.php` file.

~~~
'aliases' => array(

    'iLdap' => 'Teepluss\Ldap\Facades\iLdap'

)
~~~

Publish config using artisan CLI.

~~~
php artisan config:publish teepluss/ldap
~~~