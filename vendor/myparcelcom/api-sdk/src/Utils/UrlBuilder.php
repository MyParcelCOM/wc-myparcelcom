<?php

namespace MyParcelCom\ApiSdk\Utils;

class UrlBuilder
{
    /** @var string */
    protected $url;

    /** @var string */
    protected $scheme;

    /** @var string */
    protected $host;

    /** @var int */
    protected $port;

    /** @var string */
    protected $user;

    /** @var string */
    protected $password;

    /** @var string */
    protected $path;

    /** @var array */
    protected $query = [];

    /** @var string */
    protected $fragment;

    /**
     * @param string $url
     */
    public function __construct($url = null)
    {
        if ($url !== null) {
            $this->setUrl($url);
        }
    }

    /**
     * Set the base url.
     *
     * @param string $url
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;
        $parts = parse_url($url);

        $this->scheme = isset($parts['scheme']) ? $parts['scheme'] : null;
        $this->host = isset($parts['host']) ? $parts['host'] : null;
        $this->port = isset($parts['port']) ? $parts['port'] : null;
        $this->user = isset($parts['user']) ? $parts['user'] : null;
        $this->password = isset($parts['pass']) ? $parts['pass'] : null;
        $this->path = isset($parts['path']) ? $parts['path'] : null;
        $this->fragment = isset($parts['fragment']) ? $parts['fragment'] : null;

        if (isset($parts['query'])) {
            parse_str($parts['query'], $this->query);
        }

        return $this;
    }

    /**
     * Get the compiled url.
     *
     * @return string
     */
    public function getUrl()
    {
        $url = '';
        if ($this->scheme) {
            $url .= $this->scheme . '://';
        }

        if ($this->user && $this->password) {
            $url .= $this->user . ':' . $this->password . '@';
        } elseif ($this->user) {
            $url .= $this->user . '@';
        }

        if ($this->host) {
            $url .= $this->host;
        }
        if ($this->port) {
            $url .= ':' . $this->port;
        }
        if ($this->path) {
            $url .= $this->path;
        }
        if ($this->query) {
            $url .= '?' . urldecode(http_build_query($this->query));
        }
        if ($this->fragment) {
            $url .= '#' . $this->fragment;
        }

        return $url;
    }

    /**
     * Get the GET query params.
     *
     * @return array
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Set the GET query params.
     *
     * @param array $query
     * @return $this
     */
    public function setQuery(array $query)
    {
        $this->query = $query;

        return $this;
    }

    /**
     * Add GET query params.
     *
     * @param array $query
     * @return $this
     */
    public function addQuery(array $query)
    {
        $this->query = array_merge($this->query, $query);

        return $this;
    }

    /**
     * Get the scheme of the url.
     *
     * @return string|null
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * Set the scheme of the url.
     *
     * @param string $scheme
     * @return $this
     */
    public function setScheme($scheme)
    {
        $this->scheme = $scheme;

        return $this;
    }

    /**
     * Get the host of the url.
     *
     * @return string|null
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Set the host of the url.
     *
     * @param string $host
     * @return $this
     */
    public function setHost($host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * Get the url port.
     *
     * @return int|null
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Set the url port
     *
     * @param int $port
     * @return $this
     */
    public function setPort($port)
    {
        $this->port = $port;

        return $this;
    }

    /**
     * Get the HTTP auth user.
     *
     * @return string|null
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set the HTTP auth user.
     *
     * @param string $user
     * @return $this
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get the HTTP auth password.
     *
     * @return string|null
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set the HTTP auth password.
     *
     * @param string $password
     * @return $this
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get the path in the url.
     *
     * @return string|null
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set the url path.
     *
     * @param string $path
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get the string after `#` in the url.
     *
     * @return string|null
     */
    public function getFragment()
    {
        return $this->fragment;
    }

    /**
     * Set the string after `#` in the url.
     *
     * @param string $fragment
     * @return $this
     */
    public function setFragment($fragment)
    {
        $this->fragment = $fragment;

        return $this;
    }

    /**
     * Get the compiled url string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getUrl();
    }
}
