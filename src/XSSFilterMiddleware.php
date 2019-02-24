<?php

namespace Alkhwlani\XssMiddleware;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Foundation\Http\Middleware\TransformsRequest;

class XSSFilterMiddleware extends TransformsRequest
{
    /**
     * @var \Alkhwlani\XssMiddleware\Repository
     */
    protected $config;

    /**
     * @var \GrahamCampbell\SecurityCore\Security
     */
    private $security;

    public function __construct(Repository $config)
    {
        $this->security = app('security');
        $this->config = $config->get('xss-middleware');
    }

    /**
     * Transform the given value.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return mixed
     */
    protected function transform($key, $value)
    {
        if (! is_string($value) || in_array($key, $this->config['except'], true)) {
            return $value;
        }

        return $this->security->clean($value);
    }
}
