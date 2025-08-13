<?php

declare(strict_types=1);

/**
 * -------------------------------------------
 * Simple Dependency Injection Container
 * -------------------------------------------
 * 
 * Features:
 * - Normal binding (always returns new instances)
 * - Singleton binding (returns the same instance every time)
 * - Binding via class name or closure
 * - Auto-wiring (resolves dependencies using Reflection)
 * - Supports default parameter values
 * - Nested dependency resolution
 * 
 * Author: Tushar Nain (refactored with full documentation)
 */

// ============================================================================
// 1. Dependency Injection Container
// ============================================================================
class DIContainer
{
    /** @var array<string, callable|string> Normal bindings */
    private array $bindings = [];

    /** @var array<string, callable|string> Singleton bindings */
    private array $singletons = [];

    /** @var array<string, object> Singleton instances cache */
    private array $instances = [];

    /**
     * Bind an abstract (interface/class) to a concrete implementation or factory closure.
     *
     * @param string              $abstract Interface or abstract class name
     * @param callable|string     $concrete Either:
     *                                      - Fully qualified class name
     *                                      - Closure returning an instance
     */
    public function bind(string $abstract, callable|string $concrete): void
    {
        $this->bindings[$abstract] = $concrete;
    }

    /**
     * Bind as singleton (only created once).
     *
     * @param string              $abstract Interface or abstract class name
     * @param callable|string     $concrete Class name or closure
     */
    public function singleton(string $abstract, callable|string $concrete): void
    {
        $this->singletons[$abstract] = $concrete;
    }

    /**
     * Resolve a class or interface.
     *
     * @param string $abstract Interface or class name
     * @return object Instance of the resolved type
     */
    public function resolve(string $abstract): object
    {
        // If singleton instance already exists → return it
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        // If singleton binding exists → create once and store
        if (isset($this->singletons[$abstract])) {
            $this->instances[$abstract] = $this->build($this->singletons[$abstract]);
            return $this->instances[$abstract];
        }

        // If normal binding exists → create every time
        if (isset($this->bindings[$abstract])) {
            return $this->build($this->bindings[$abstract]);
        }

        // No binding → try to auto-resolve (auto-wiring)
        return $this->autoResolve($abstract);
    }

    /**
     * Build from binding (string or callable).
     */
    private function build(callable|string $concrete): object
    {
        if (is_callable($concrete)) {
            return $concrete($this); // Pass container to closure
        }

        // If it's a class name → auto-resolve it
        return $this->resolve($concrete);
    }

    /**
     * Auto-resolve class dependencies using Reflection.
     *
     * @throws ReflectionException
     */
    private function autoResolve(string $abstract): object
    {
        $reflectionClass = new ReflectionClass($abstract);

        // If no constructor → no dependencies → create directly
        if (!$reflectionClass->getConstructor()) {
            return new $abstract();
        }

        $dependencies = [];

        foreach ($reflectionClass->getConstructor()->getParameters() as $param) {
            $type = $param->getType();

            // If type is not a class (primitive type) → use default if available
            if (!$type || $type->isBuiltin()) {
                $dependencies[] = $param->isDefaultValueAvailable()
                    ? $param->getDefaultValue()
                    : null;
                continue;
            }

            // Resolve class/interface dependency recursively
            $dependencies[] = $this->resolve($type->getName());
        }

        return $reflectionClass->newInstanceArgs($dependencies);
    }
}

// ============================================================================
// 2. Example Interfaces & Implementations
// ============================================================================
interface LoggerInterface
{
    public function log(string $msg): void;
}

readonly class FileLogger implements LoggerInterface
{
    public function log(string $msg): void
    {
        echo "[FileLogger] $msg\n";
    }
}

readonly class DatabaseLogger implements LoggerInterface
{
    public function log(string $msg): void
    {
        echo "[DatabaseLogger] $msg\n";
    }
}

// ============================================================================
// 3. Example Services (Dependencies)
// ============================================================================
readonly class UserService
{
    public function __construct(private LoggerInterface $logger) {}

    public function createUser(string $name): void
    {
        $this->logger->log("User '{$name}' created successfully.");
    }
}

readonly class ReportService
{
    public function __construct(
        private LoggerInterface $logger,
        private string $reportFormat = 'PDF' // Default primitive value
    ) {}

    public function generate(): void
    {
        $this->logger->log("Report generated in {$this->reportFormat} format.");
    }
}

readonly class AdminService
{
    public function __construct(private UserService $userService, private ReportService $reportService) {}

    public function performAdminTask(): void
    {
        $this->userService->createUser("AdminUser");
        $this->reportService->generate();
    }
}

// ============================================================================
// 4. Demonstration of DI Features
// ============================================================================
$container = new DIContainer();

// -------------------------------------------
// Example 1: Normal binding (always new instance)
// -------------------------------------------
$container->bind(LoggerInterface::class, DatabaseLogger::class);

echo "---- Normal Binding ----\n";
$userService1 = $container->resolve(UserService::class);
$userService1->createUser("Tushar");

$userService2 = $container->resolve(UserService::class);
$userService2->createUser("Amit");
// Note: Logger will be a NEW instance each time

// -------------------------------------------
// Example 2: Singleton binding (same instance reused)
// -------------------------------------------
$container->singleton(LoggerInterface::class, FileLogger::class);

echo "---- Singleton Binding ----\n";
$userService3 = $container->resolve(UserService::class);
$userService3->createUser("Raj");

$userService4 = $container->resolve(UserService::class);
$userService4->createUser("Priya");
// Note: Logger will be the SAME instance as above

// -------------------------------------------
// Example 3: Binding via Closure (custom factory)
// -------------------------------------------
$container->singleton(LoggerInterface::class, function () {
    // Could fetch config, do setup, etc.
    return new FileLogger();
});

echo "---- Closure Binding ----\n";
$userService5 = $container->resolve(UserService::class);
$userService5->createUser("CustomFactoryUser");

// -------------------------------------------
// Example 4: Auto-wiring (no binding defined)
// -------------------------------------------
echo "---- Auto-wiring ----\n";
$reportService = $container->resolve(ReportService::class);
$reportService->generate(); // Will inject LoggerInterface (last bound)

// -------------------------------------------
// Example 5: Nested dependencies
// -------------------------------------------
echo "---- Nested Dependencies ----\n";
$adminService = $container->resolve(AdminService::class);
$adminService->performAdminTask();

// ============================================================================
// End of Demonstration
// ============================================================================
