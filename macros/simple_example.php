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
        self::$macros[$name] = $callback;
    }

    // ─────────────────────────────────────────────────────────────
    // SECTION: Core Arithmetic Methods
    // ─────────────────────────────────────────────────────────────

    /**
     * Add two integers.
     *
     * @param int $num1
     * @param int $num2
     * @return int
     */
    public function sum(int $num1, int $num2): int
    {
        return $num1 + $num2;
    }

    /**
     * Subtract two integers.
     *
     * @param int $num1
     * @param int $num2
     * @return int
     */
    public function subtract(int $num1, int $num2): int
    {
        return $num1 - $num2;
    }

    /**
     * Multiply two integers.
     *
     * @param int $num1
     * @param int $num2
     * @return int
     */
    public function multiply(int $num1, int $num2): int
    {
        return $num1 * $num2;
    }

    /**
     * Divide two integers and return a float.
     *
     * @param int $num1
     * @param int $num2
     * @return float
     * @throws InvalidArgumentException
     */
    public function divide(int $num1, int $num2): float
    {
        if ($num2 === 0) {
            throw new InvalidArgumentException("Division by zero is not allowed.");
        }

        return $num1 / $num2;
    }

    // ─────────────────────────────────────────────────────────────
    // SECTION: Macro Handling via Magic Call
    // ─────────────────────────────────────────────────────────────

    /**
     * Handle calls to undefined methods via registered macros.
     *
     * @param string $method
     * @param array $args
     * @return mixed
     * @throws BadMethodCallException
     */
    public function __call($method, $args)
    {
        if (isset(self::$macros[$method])) {
            // Binds macro to current instance for $this support
            return self::$macros[$method]->call($this, ...$args);
        }

        throw new BadMethodCallException("Method [$method] does not exist.");
    }
}



// ─────────────────────────────────────────────────────────────
// SECTION: Macro Definitions
// ─────────────────────────────────────────────────────────────

/**
 * Macro: power
 * Description: Raise base to the exponent power.
 */
Calculator::macro('power', function(int $base, int $exponent): int {
    return $base ** $exponent;
});

/**
 * Macro: average
 * Description: Calculates the average of any number of integers.
 */
Calculator::macro('average', function(int ...$numbers): float {
    $count = count($numbers);

    if ($count === 0) {
        throw new InvalidArgumentException("At least one number is required to calculate average.");
    }

    return array_sum($numbers) / $count;
});

/**
 * Macro: factorial
 * Description: Calculates factorial recursively.
 * Supports positive integers only.
 */
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
    
    $result = $calculator->power(2, 4); // Should return 16
    echo 'The result is ' . $result . PHP_EOL;

    echo 'Average: ' . $calculator->average(10, 20, 30) . PHP_EOL;
    echo 'Factorial of 5: ' . $calculator->factorial(5) . PHP_EOL;

} catch (BadMethodCallException | InvalidArgumentException $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
}
