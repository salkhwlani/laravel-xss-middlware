<?php

namespace Alkhwlani\XssMiddleware;

use voku\helper\AntiXSS;
use voku\helper\UTF8;

/**
 * Drop-in replacement for the previous graham-campbell/security-core
 * `Security` class. Resolved via the `app('security')` binding; consumers
 * upgrading from v4 only need to swap their `use` statement to this
 * class (or use `app('security')` and avoid the type-hint entirely).
 */
class Security
{
    public function __construct(private readonly AntiXSS $antiXss)
    {
    }

    /**
     * Sanitize the given value.
     *
     * Runs the value through voku/anti-xss, strips C0 control characters,
     * and — if stripping changed the string — re-runs voku to defend
     * against payloads that were hiding behind invisible characters.
     *
     * @param  string|array<int|string, mixed>  $input
     * @return string|array<int|string, mixed>
     */
    public function clean(mixed $input): mixed
    {
        $output = $this->antiXss->xss_clean($input);

        if ($this->antiXss->isXssFound() === true) {
            return $output;
        }

        $stripped = self::cleanInvisibleCharacters($output);

        // Re-clean only if invisible-character stripping mutated the value
        // — a payload may have been concealed behind C0 control bytes.
        return $stripped == $output ? $output : $this->antiXss->xss_clean($stripped);
    }

    /**
     * @param  string|array<int|string, mixed>  $input
     * @return string|array<int|string, mixed>
     */
    private static function cleanInvisibleCharacters(mixed $input): mixed
    {
        if (is_array($input)) {
            foreach ($input as $key => &$value) {
                $value = self::cleanInvisibleCharacters($value);
            }

            return $input;
        }

        if (! is_string($input)) {
            return $input;
        }

        return UTF8::remove_invisible_characters($input, true);
    }
}
