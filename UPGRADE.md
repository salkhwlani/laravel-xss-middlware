# Upgrade guide

## From v4 to v5

### Backend swap

The package no longer depends on `graham-campbell/security` / `graham-campbell/security-core`. The XSS sanitizer is now `voku/anti-xss` directly. Same underlying engine — graham-campbell was already a thin wrapper around voku — but the public surface that came with the wrapper is gone.

### Drop-in for `app('security')` — no change required

`app('security')` still resolves to a `Security` instance with a `clean()` method that behaves identically to `GrahamCampbell\SecurityCore\Security::clean()`:

- `xss_clean()` then C0 control-character strip (raw + URL-encoded), preserving TAB / LF / CR
- if the strip mutated the value, re-run `xss_clean()` (defends against payloads hidden behind invisible chars)

If you only ever call `app('security')->clean(...)`, **no code changes are needed**.

### Drop-in for type-hints — swap the `use` statement

If you were type-hinting `\GrahamCampbell\SecurityCore\Security` (constructor injection, controller method parameter, factory pattern, etc.), update your imports:

```diff
- use GrahamCampbell\SecurityCore\Security;
+ use Alkhwlani\XssMiddleware\Security;
```

The class shape, constructor, and `clean()` semantics match the original.

### Subclasses overriding `transform()`

If you subclass `XSSFilterMiddleware` and override `transform()`, `$this->security` is still a `Security` instance with a `clean()` method — same as v4. `$this->security->clean($value)` continues to work.

### Facade users — install graham-campbell/security separately

This package no longer ships the `GrahamCampbell\Security\Facades\Security` facade.

If your code uses that facade (e.g. `Security::clean(...)` via `use GrahamCampbell\Security\Facades\Security;`):

**On Laravel 10 / 11** — install `graham-campbell/security` directly to keep the facade:

```bash
composer require graham-campbell/security:^11.0
```

The facade keeps working. graham-campbell's service provider re-binds `'security'` after ours, so `Security::clean(...)` via the facade resolves through their wrapper. The middleware itself is unaffected — it resolves `Alkhwlani\XssMiddleware\Security` via its FQCN, not via the `'security'` alias.

**On Laravel 12 / 13** — `graham-campbell/security` does not support these versions. Migrate the facade calls to either:

```php
// option A — type-hint our Security class
use Alkhwlani\XssMiddleware\Security;

class Foo {
    public function __construct(private Security $security) {}
    public function bar() { $this->security->clean($input); }
}

// option B — resolve from the container
app('security')->clean($input);
```

### `Security::create($evil, $replacement)` static factory

The static factory is not provided by this package. The same configuration is now done declaratively via `config/xss-middleware.php`:

```php
'evil' => [
    'attributes'         => [...],   // addEvilAttributes
    'tags'               => [...],   // addEvilHtmlTags
    'regex'              => [...],   // addNeverAllowedRegex
    'events'             => [...],   // addNeverAllowedOnEventsAfterwards
    'strAfterwards'      => [...],   // addNeverAllowedStrAfterwards
    'doNotCloseHtmlTags' => [...],   // addDoNotCloseHtmlTags
],
'replacement' => '...',
```

The values are applied to the `voku\helper\AntiXSS` singleton on first resolve.

### Config publishing

Re-publish the config to pick up the new `evil` / `replacement` keys:

```bash
php artisan vendor:publish --provider="Alkhwlani\\XssMiddleware\\ServiceProvider" --tag=config --force
```

### `evil` config shape

The `evil` key now expects an associative array:

```php
'evil' => [
    'attributes' => ['style'],
    'tags'       => ['svg'],
],
```

A flat list of attribute names (the old graham-campbell single-arg shape) is still accepted, but emits an `E_USER_DEPRECATED` notice and will be removed in a future major. Update your published config to the associative shape.
