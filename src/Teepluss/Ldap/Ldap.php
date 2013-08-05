<?php namespace Teepluss\Ldap;

use Illuminate\Cookie\CookieJar;
use Illuminate\Config\Repository;
use Illuminate\Session\Store as SessionStore;

class Ldap {

    /**
     * The Illuminate config service.
     *
     * @var \Illuminate\Config\Repository
     */
    protected $config;

    /**
     * The Illuminate session service.
     *
     * @var \Illuminate\Session\Store
     */
    protected $session;

    /**
     * The Illuminate cookie creator service.
     *
     * @var \Illuminate\Cookie\CookieJar
     */
    protected $cookie;

    /**
     * Ldap profile connections.
     *
     * @var array
     */
    protected $connections;

    /**
     * Current Ldap profile.
     *
     * @var string
     */
    protected $profile;

    /**
     * Ldap connection.
     *
     * @var resource
     */
    protected $ds;

    /**
     * The cookies queued by the guards.
     *
     * @var array
     */
    protected $queuedCookies = array();

    /**
     * Create a new Ldap.
     *
     * @param  \Illuminate\Config\Repository  $config
     * @param  \Illuminate\Session\Store  $session
     * @param  \Illuminate\Cookie\CookieJar  $cookie
     * @return void
     */
    public function __construct(Repository $config, SessionStore $session, CookieJar $cookie)
    {
        $this->config = $config;

        $this->session = $session;

        $this->cookie = $cookie;
    }

    /**
     * Add Ldap connections from service provider.
     *
     * @param array $connections
     */
    public function addConnections($connections)
    {
        $this->connections = $connections;

        return $this;
    }

    /**
     * Connect to Ldap server.
     *
     * @param  string $profile
     * @return Ldap
     */
    public function connect($profile)
    {
        $this->profile = $this->connections[$profile];

        $this->ds = ldap_connect($this->profile['host']) or die("Could not connect to $ldaphost");

        return $this;
    }

    /**
     * Ldap authenticate.
     *
     * @param  string  $username
     * @param  string  $password
     * @param  boolean $remember
     * @return boolean
     */
    public function authenticate($username, $password, $remember = false)
    {
        $credentials = array('username' => $username, 'password' => $password);

        if ( ! $this->bind($credentials))
        {
            throw new UserNotFoundException('User ['.$username.'] not found.');
        }

        $this->login($credentials, $remember);

        return true;
    }

    /**
     * Ldap bind.
     *
     * @param  array  $credentials
     * @return resource
     */
    protected function bind(array $credentials)
    {
        $dn = 'uid='.$credentials['username'].',ou='.$this->profile['ou']['users'].','.$this->profile['base'];

        return @ldap_bind($this->ds, $dn, $credentials['password']);
    }

    /**
     * Get credentials.
     *
     * @return array
     */
    protected function getCredentials()
    {
        $cookie = $this->cookie;

        $credentials = $this->session->get('credentials', function() use ($cookie)
        {
            return $cookie->get('credentials');
        });

        return $credentials;
    }

    /**
     * Ldap bined.
     *
     * @return resource
     */
    protected function binded()
    {
        $credentials = $this->getCredentials();

        return $this->bind($credentials);
    }

    /**
     * Get cookies.
     *
     * @return array
     */
    public function getQueuedCookies()
    {
        return $this->queuedCookies;
    }

    /**
     * Login to Ldap server.
     *
     * @param  array   $credentials
     * @param  boolean $remember
     * @return void
     */
    public function login($credentials, $remember)
    {
        // Set sessions
        $this->session->put('credentials', $credentials);

        if ($remember)
        {
            //$this->cookie->forever('credentials', $credentials);
            $this->queuedCookies[] = $this->cookie->forever('credentials', $credentials);
        }
    }

    /**
     * Filter Ldap.
     *
     * @param  mixed $filter
     * @return object
     */
    public function find($filter)
    {
        if ($this->binded())
        {
            $base = 'ou='.$this->profile['ou']['users'].','.$this->profile['base'];

            $sr = ldap_list($this->ds, $base, $filter);

            return ldap_get_entries($this->ds, $sr);
        }
    }

    /**
     * Find user.
     *
     * @param  integer $user
     * @return object
     */
    public function findUser($user)
    {
        return $this->convert($this->find('uid='.$user));
    }

    /**
     * Find all users.
     *
     * @return object
     */
    public function findAllUsers()
    {
        return $this->findUser('*');
    }

    /**
     * Get authenticated user.
     *
     * @return object
     */
    public function getUser()
    {
        $credentials = $this->getCredentials();

        if ($credentials)
        {
            $user = $credentials['username'];

            return $this->findUser($user);
        }
    }

    /**
     * Check authentication.
     *
     * @return boolean
     */
    public function check()
    {
        return (boolean) $this->getCredentials();
    }

    /**
     * Conver scheams.
     *
     * @param  mixed $entries
     * @return mixed
     */
    public function convert($entries)
    {
        $stacks = array();

        $schemas = $this->config->get('ldap::schemas');

        return $schemas($entries);
    }

    /**
     * Logout from Ldap server.
     * @return void
     */
    public function logout()
    {
        $this->session->forget('credentials');

        $this->queuedCookies[] = $this->cookie->forget('credentials');
    }

}