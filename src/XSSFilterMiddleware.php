<?php

namespace Alkhwlani\XssMiddleware;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Foundation\Http\Middleware\TransformsRequest;

class XSSFilterMiddleware extends TransformsRequest
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @var Security
     */
    protected $security;

    public function __construct(Repository $config)
    {
        $this->security = app(Security::class);
        $this->config = $config->get('xss-middleware');
    }

    /**
     * Transform the given value.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return mixed
     */
    protected function transform($key, $value)
    {
        if ($this->shouldIgnore($key, $value)) {
            return $value;
        }

        return $this->security->clean($value);
    }

    /**
     * determine if should ignore the field.
     */
    protected function shouldIgnore($key, $value): bool
    {
        return ! is_string($value) || in_array($key, $this->config['except'], true);
    }
}
