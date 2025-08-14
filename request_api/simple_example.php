<?php

/**
 * Class Request
 *
 * A basic HTTP Request representation.
 * Can be created empty or populated from PHP superglobals.
 * Designed to be extendable for specialized request handling.
 */
class Request
{
    // ===============================
    // HTTP Method Constants
    // ===============================

    public const string METHOD_GET     = 'GET';
    public const string METHOD_POST    = 'POST';
    public const string METHOD_PUT     = 'PUT';
    public const string METHOD_DELETE  = 'DELETE';
    public const string METHOD_PATCH   = 'PATCH';
    public const string METHOD_OPTIONS = 'OPTIONS';
    public const string METHOD_HEAD    = 'HEAD';

    /**
     * List of all supported HTTP methods.
     *
     * @var string[]
     */
    public const METHODS = [
        self::METHOD_GET,
        self::METHOD_POST,
        self::METHOD_PUT,
        self::METHOD_DELETE,
        self::METHOD_PATCH,
        self::METHOD_OPTIONS,
        self::METHOD_HEAD,
    ];

    // ===============================
    // Request Data Storage
    // ===============================

    /** @var array<string, mixed> */
    protected array $get = [];

    /** @var array<string, mixed> */
    protected array $post = [];

    /** @var array<string, mixed> */
    protected array $server = [];

    /** @var array<string, mixed> */
    protected array $cookies = [];

    /** @var array<string, mixed> */
    protected array $files = [];

    /**
     * Request constructor.
     *
     * @param array<string, mixed> $get
     * @param array<string, mixed> $post
     * @param array<string, mixed> $server
     * @param array<string, mixed> $cookies
     * @param array<string, mixed> $files
     */
    public function __construct(
        array $get = [],
        array $post = [],
        array $server = [],
        array $cookies = [],
        array $files = []
    ) {
        $this->get     = $get;
        $this->post    = $post;
        $this->server  = $server;
        $this->cookies = $cookies;
        $this->files   = $files;
    }

    /**
     * Create a new Request instance from PHP superglobals.
     *
     * @return static
     */
    public static function createFromGlobals(): static
    {
        return new static($_GET, $_POST, $_SERVER, $_COOKIE, $_FILES);
    }

    /**
     * Get the HTTP request method (e.g., GET, POST).
     *
     * @return string
     */
    public function getMethod(): string
    {
        return strtoupper($this->server['REQUEST_METHOD'] ?? static::METHOD_GET);
    }

    /**
     * Check if the request method matches a given method.
     *
     * @param string $method
     * @return bool
     */
    public function isMethod(string $method): bool
    {
        return $this->getMethod() === strtoupper($method);
    }

    /**
     * Get the request URI without query string.
     *
     * @return string
     */
    public function getUri(): string
    {
        return strtok($this->server['REQUEST_URI'] ?? '/', '?');
    }

    /**
     * Get a query string parameter value.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getQuery(string $key, $default = null)
    {
        return $this->get[$key] ?? $default;
    }

    /**
     * Get a POST parameter value.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getPost(string $key, $default = null)
    {
        return $this->post[$key] ?? $default;
    }

    /**
     * Get all request parameters (query + post).
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return array_merge($this->get, $this->post);
    }

    /**
     * Get a header value by name.
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getHeader(string $name, $default = null)
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        
        return $this->server[$key] ?? $default;
    }

    /**
     * Get the client IP address.
     *
     * @return string
     */
    public function getIp(): string
    {
        return $this->server['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}
