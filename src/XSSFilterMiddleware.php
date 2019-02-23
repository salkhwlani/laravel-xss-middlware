<?php

namespace Alkhwlani\XssMiddleware;

use Illuminate\Foundation\Http\Middleware\TransformsRequest;

class XSSFilterMiddleware extends TransformsRequest
{
    /**
     * @var \GrahamCampbell\SecurityCore\Security
     */
    private $security;

    public function __construct()
    {
        $this->security = app('security');
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
        if (! is_string($value)) {
            return $value;
        }

        return $this->security->clean($value);
    }
}
