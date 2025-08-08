# 🔧 PHP Macros – Dynamic Method Extensions in PHP

This project demonstrates how to build a **macro system** in PHP - allowing classes to register and call methods **dynamically at runtime**.

Inspired by Laravel’s `Macroable` trait, this pattern provides flexible and powerful extensibility without inheritance.

---

## 🚀 What Are Macros?

**Macros** allow you to dynamically attach methods to a class or object at runtime.  
This is especially useful in frameworks, libraries, or tools where you want users to **extend behavior without modifying core code**.

---

## ✅ Why Use Macros?

- Add new methods to existing classes **without subclassing**
- Build **extensible frameworks** or tools
- Follow the **Open/Closed Principle** - open for extension, closed for modification
- Share reusable behaviors dynamically
- Enable **plugin-style** architectures

---

## 🧠 Real-World Usage

**Laravel** uses macros in classes like:

- `Illuminate\Support\Str`
- `Illuminate\Support\Collection`
- `Illuminate\Routing\ResponseFactory`

You can do:

```php
Response::macro('caps', fn($text) => strtoupper($text));
echo response()->caps('hello'); // HELLO
```

This project demonstrates how to build such a system yourself.

---

## 📦 Example: Macroable Trait

```php
trait Macroable
{
    protected static array $macros = [];

    public static function macro(string $name, callable $callback): void
    {
        static::$macros[$name] = $callback;
    }

    public static function hasMacro(string $name): bool
    {
        return isset(static::$macros[$name]);
    }

    public function __call(string $method, array $parameters)
    {
        if (! static::hasMacro($method)) {
            throw new BadMethodCallException("Method [$method] does not exist.");
        }

        return static::$macros[$method]->bindTo($this, static::class)(...$parameters);
    }

    public static function __callStatic(string $method, array $parameters)
    {
        if (! static::hasMacro($method)) {
            throw new BadMethodCallException("Static method [$method] does not exist.");
        }

        return static::$macros[$method](...$parameters);
    }
}
```

---

## ⚙️ How to Use

### Step 1: Add the Trait

```php
class Greeting
{
    use Macroable;
}
```

---

### Step 2: Register Macros

```php
Greeting::macro('greet', function ($name) {
    return "Hello, $name!";
});
```

---

### Step 3: Call Them (Static or Instance)

```php
echo Greeting::greet('Tushar'); // Hello, Tushar

$greet = new Greeting();
echo $greet->greet('World'); // Hello, World
```

---

## 🛠️ Advanced Example: Calculator

```php
class Calculator
{
    use Macroable;

    public function sum($a, $b) {
        return $a + $b;
    }
}

Calculator::macro('power', function ($base, $exp) {
    return $base ** $exp;
});

Calculator::macro('factorial', function ($n) {
    return $n <= 1 ? 1 : $n * $this->factorial($n - 1);
});

$calc = new Calculator();
echo $calc->power(2, 3);     // 8
echo $calc->factorial(5);    // 120
```

---

## 🧱 Why Macros Are Essential in Frameworks

### ✅ Runtime Extensibility

Allow users or packages to add methods to classes *without modifying them*.

### ✅ Avoid Inheritance Hell

No need to subclass everything just to add behavior.

### ✅ Plugin Architecture

Macros enable reusable features to be "plugged into" a class.

---

## ⚠️ Limitations

- Macros are not discoverable via IDEs or static analysis
- Can be abused and lead to hard-to-read code
- Requires careful naming to avoid collisions

---

## 🧪 Test It

```bash
php -f example.php
```

Or run in interactive mode:

```bash
php -a
```

---

## 📎 License

MIT - use freely, improve responsibly.
