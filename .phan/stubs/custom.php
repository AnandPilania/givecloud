<?php

namespace {
    /**
     * @param int $code
     * @param string $message
     * @param array $headers
     * @return void
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    function abort($code, $message = '', $headers = array()) {}

    /**
     * @param \ArrayAccess|array $array
     * @param string|int $key
     * @param mixed $default
     * @return mixed
     */
    function array_get($array, $key, $default = null) {}
}
