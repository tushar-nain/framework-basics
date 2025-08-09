<?php

declare(strict_types = 1);

trait Macroable
{
    /**
     * The registered string macros.
     *
     * @var array
     */
    protected static array $macros = [];

    /**
     * Register a custom macro.
     *
     * @param string $name
     * @param callable $macro
     * @return void
     */
    public static function macro(string $name, callable $macro): void
    {
        static::$macros[$name] = $macro;
    }

    /**
     * Check if macro is registered.
     *
     * @param string $name
     * @return bool
     */
    public static function hasMacro(string $name): bool
    {
        return isset(static::$macros[$name]);
    }

    /**
     * Dynamically call a macro on the class instance.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call(string $method, array $parameters)
    {
        if (!static::hasMacro($method)) {
            throw new BadMethodCallException("Method {$method} does not exist.");
        }

        $macro = static::$macros[$method];

        if ($macro instanceof Closure) {
            return $macro->bindTo($this, static::class)(...$parameters);
        }

        return $macro(...$parameters);
    }

    /**
     * Dynamically call a macro on the class (statically).
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public static function __callStatic(string $method, array $parameters)
    {
        if (!static::hasMacro($method)) {
            throw new BadMethodCallException("Static method {$method} does not exist.");
        }

        $macro = static::$macros[$method];

        return $macro(...$parameters);
    }
}

class Greeting
{
    use Macroable;
}

// Registering a macro
Greeting::macro('hello', function ($name) {
    return "Hello, {$name}!";
});

// Calling it statically
echo Greeting::hello('Tushar') . PHP_EOL; // Outputs: Hello, Tushar!

// Or use on instance
$greet = new Greeting;
echo $greet->hello('Tushar') . PHP_EOL; // Also works
