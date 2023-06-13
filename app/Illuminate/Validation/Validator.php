<?php

namespace Ds\Illuminate\Validation;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator as IlluminateValidator;

class Validator extends IlluminateValidator
{
    /**
     * Validate that an attribute exists when another attribute has a given value.
     *
     * @param string $attribute
     * @param mixed $value
     * @param mixed $parameters
     * @return bool
     */
    public function validateRequiredIf($attribute, $value, $parameters)
    {
        $this->requireParameterCount(2, $parameters, 'required_if');

        $other = Arr::get($this->data, $parameters[0]);

        $values = array_slice($parameters, 1);

        if (is_bool($other)) {
            $values = $this->convertValuesToBoolean($values);
        }

        if (in_array($other, $values)) {
            return $this->validateRequired($attribute, $value);
        }

        $this->rules[$attribute][] = 'stop_validating';

        return true;
    }

    /**
     * Validate that an attribute exists when another attribute does not have a given value.
     *
     * @param string $attribute
     * @param mixed $value
     * @param mixed $parameters
     * @return bool
     */
    public function validateRequiredUnless($attribute, $value, $parameters)
    {
        $this->requireParameterCount(2, $parameters, 'required_unless');

        $data = Arr::get($this->data, $parameters[0]);

        $values = array_slice($parameters, 1);

        if (! in_array($data, $values)) {
            return $this->validateRequired($attribute, $value);
        }

        $this->rules[$attribute][] = 'stop_validating';

        return true;
    }

    /**
     * Validate that an attribute exists when any other attribute exists.
     *
     * @param string $attribute
     * @param mixed $value
     * @param mixed $parameters
     * @return bool
     */
    public function validateRequiredWith($attribute, $value, $parameters)
    {
        if (! $this->allFailingRequired($parameters)) {
            return $this->validateRequired($attribute, $value);
        }

        $this->rules[$attribute][] = 'stop_validating';

        return true;
    }

    /**
     * Validate that an attribute exists when all other attributes exists.
     *
     * @param string $attribute
     * @param mixed $value
     * @param mixed $parameters
     * @return bool
     */
    public function validateRequiredWithAll($attribute, $value, $parameters)
    {
        if (! $this->anyFailingRequired($parameters)) {
            return $this->validateRequired($attribute, $value);
        }

        $this->rules[$attribute][] = 'stop_validating';

        return true;
    }

    /**
     * Validate that an attribute exists when another attribute does not.
     *
     * @param string $attribute
     * @param mixed $value
     * @param mixed $parameters
     * @return bool
     */
    public function validateRequiredWithout($attribute, $value, $parameters)
    {
        if ($this->anyFailingRequired($parameters)) {
            return $this->validateRequired($attribute, $value);
        }

        $this->rules[$attribute][] = 'stop_validating';

        return true;
    }

    /**
     * Validate that an attribute exists when all other attributes do not.
     *
     * @param string $attribute
     * @param mixed $value
     * @param mixed $parameters
     * @return bool
     */
    public function validateRequiredWithoutAll($attribute, $value, $parameters)
    {
        if ($this->allFailingRequired($parameters)) {
            return $this->validateRequired($attribute, $value);
        }

        $this->rules[$attribute][] = 'stop_validating';

        return true;
    }

    /**
     * "Break" on validation fail.
     *
     * Always returns true, just lets us put "stop_validating" in rules.
     *
     * @return bool
     */
    public function validateStopValidating()
    {
        return true;
    }

    /**
     * Check if we should stop further validations on a given attribute.
     *
     * @param string $attribute
     * @return bool
     */
    protected function shouldStopValidating($attribute)
    {
        if ($this->hasRule($attribute, ['StopValidating'])) {
            return true;
        }

        return parent::shouldStopValidating($attribute);
    }

    /**
     * Replace all error message place-holders with actual values.
     *
     * @param string $message
     * @param string $attribute
     * @param string $rule
     * @param array $parameters
     * @return string
     */
    public function makeReplacements($message, $attribute, $rule, $parameters)
    {
        $message = parent::makeReplacements($message, $attribute, $rule, $parameters);

        if (! Str::contains($message, ':value')) {
            return $message;
        }

        try {
            $value = (string) $this->getValue($attribute);
        } catch (\Throwable $e) {
            return $message;
        }

        return str_replace(':value', $value, $message);
    }
}
