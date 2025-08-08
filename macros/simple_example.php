<?php

/**
 * Class Calculator
 *
 * A simple calculator supporting basic arithmetic operations and dynamic macro extensions.
 */
class Calculator
{
    /**
     * Stores dynamically registered macros.
     *
     * @var array<string, Closure>
     */
    protected static array $macros = [];


    // ─────────────────────────────────────────────────────────────
    // SECTION: Macro Registration
    // ─────────────────────────────────────────────────────────────

    /**
     * Register a new macro method.
     *
     * @param string $name     Name of the method to register.
     * @param Closure $callback The logic to execute when the macro is called.
     * @return void
     */
    public static function macro(string $name, Closure $callback): void
    {
        static::$macros[$name] = $callback;
    }

    /**
     * Determine if a macro is registered.
     *
     * @param string $name
     * @return bool
     */
    public static function hasMacro(string $name): bool
    {
        return isset(static::$macros[$name]);
    }

    
    // ─────────────────────────────────────────────────────────────
    // SECTION: Core Arithmetic Methods
    // ─────────────────────────────────────────────────────────────

    public function sum(int $num1, int $num2): int
    {
        return $num1 + $num2;
    }

    public function subtract(int $num1, int $num2): int
    {
        return $num1 - $num2;
    }

    public function multiply(int $num1, int $num2): int
    {
        return $num1 * $num2;
    }

    public function divide(int $num1, int $num2): float
    {
        if ($num2 === 0) {
            throw new InvalidArgumentException("Division by zero is not allowed.");
        }

        return $num1 / $num2;
    }


    // ─────────────────────────────────────────────────────────────
    // SECTION: Macro Handling via Magic Calls
    // ─────────────────────────────────────────────────────────────

    /**
     * Handle calls to undefined instance methods via registered macros.
     *
     * @param string $method
     * @param array $args
     * @return mixed
     * @throws BadMethodCallException
     */
    public function __call($method, $args)
    {
        if (static::hasMacro($method)) {
            return static::$macros[$method]->call($this, ...$args);
        }

        throw new BadMethodCallException("Method [$method] does not exist.");
    }

    /**
     * Handle calls to undefined static methods via registered macros.
     *
     * @param string $method
     * @param array $args
     * @return mixed
     * @throws BadMethodCallException
     */
    public static function __callStatic($method, $args)
    {
        if (static::hasMacro($method)) {
            return static::$macros[$method](...$args);
        }

        throw new BadMethodCallException("Static method [$method] does not exist.");
    }
}




// ─────────────────────────────────────────────────────────────
// SECTION: Macro Definitions
// ─────────────────────────────────────────────────────────────

Calculator::macro('power', function(int $base, int $exponent): int {
    return $base ** $exponent;
});

Calculator::macro('average', function(int ...$numbers): float {
    $count = count($numbers);

    if ($count === 0) {
        throw new InvalidArgumentException("At least one number is required to calculate average.");
    }

    return array_sum($numbers) / $count;
});

Calculator::macro('factorial', function(int $n): int {
    if ($n < 0) {
        throw new InvalidArgumentException("Factorial is not defined for negative numbers.");
    }

    return $n <= 1 ? 1 : $n * $this->factorial($n - 1);
});




// ─────────────────────────────────────────────────────────────
// SECTION: Usage Example
// ─────────────────────────────────────────────────────────────

$calculator = new Calculator;

try {

    echo 'Instance Power: ' . $calculator->power(2, 4) . PHP_EOL;     // 16
    echo 'Instance Avg: ' . $calculator->average(10, 20, 30) . PHP_EOL; // 20
    echo 'Instance Fact: ' . $calculator->factorial(5) . PHP_EOL;       // 120

    echo 'Static Power: ' . Calculator::power(3, 3) . PHP_EOL;         // 27
    echo 'Static Avg: ' . Calculator::average(1, 2, 3) . PHP_EOL;       // 2

} catch (BadMethodCallException | InvalidArgumentException $e) {

    echo 'Error: ' . $e->getMessage() . PHP_EOL;

}
