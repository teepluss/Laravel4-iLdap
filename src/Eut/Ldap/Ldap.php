<?php namespace Eut\Ldap;

class Ldap {

    protected $config;

    protected $session;

    protected $cookie;

    protected $connections;

    protected $profile;

    protected $ds;

    public function __construct($config, $session, $cookie)
    {
        $this->config = $config;

        $this->session = $session;

        $this->cookie = $cookie;
    }

    public function addConnections($connections)
    {
        $this->connections = $connections;

        return $this;
    }

    public function connect($profile)
    {
        $this->profile = $this->connections[$profile];

        $this->ds = ldap_connect($this->profile['host']) or die("Could not connect to $ldaphost");

        return $this;
    }

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

    protected function bind(array $credentials)
    {
        $dn = 'uid='.$credentials['username'].',ou='.$this->profile['ou']['users'].','.$this->profile['base'];

        return @ldap_bind($this->ds, $dn, $credentials['password']);
    }

    protected function getCredentials()
    {
        $cookie = $this->cookie;

        $credentials = $this->session->get('credentials', function() use ($cookie)
        {
            return $cookie->get('credentials');
        });

        return $credentials;
    }

    protected function binded()
    {
        $credentials = $this->getCredentials();

        return $this->bind($credentials);
    }

    public function login($credentials, $remember)
    {
        // Set sessions
        $this->session->put('credentials', $credentials);

        if ($remember)
        {
            $this->cookie->forever('credentials', $credentials);
        }
    }

    public function find($filter)
    {
        if ($this->binded())
        {
            $base = 'ou='.$this->profile['ou']['users'].','.$this->profile['base'];

            $sr = ldap_list($this->ds, $base, $filter);

            return ldap_get_entries($this->ds, $sr);
        }
    }

    public function findUser($user)
    {
        return $this->convert($this->find('uid='.$user));
    }

    public function findAllUsers()
    {
        return $this->findUser('*');
    }

    public function getUser($user = null)
    {
        $credentials = $this->getCredentials();

        if (is_null($user) and $credentials)
        {
            $user = $credentials['username'];

            return $this->findUser($user);
        }

        return false;
    }

    public function check()
    {
        return (boolean) $this->getCredentials();
    }

    public function convert($entries)
    {
        $stacks = array();

        $schemas = $this->config->get('ldap::schemas');

        return $schemas($entries);
    }

    public function logout()
    {
        $this->session->forget('credentials');
        $this->cookie->forget('credentials');
    }

}