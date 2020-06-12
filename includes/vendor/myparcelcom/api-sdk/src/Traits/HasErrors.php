<?php

namespace MyParcelCom\ApiSdk\Traits;

trait HasErrors
{
    /** @var array */
    protected $errors = [];

    /**
     * Get all the errors.
     *
     * @return string[]
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Returns true if errors are set.
     *
     * @return bool
     */
    public function hasErrors()
    {
        return !empty($this->errors);
    }

    /**
     * Set all the found errors.
     *
     * @param string[] $errors
     * @return $this
     */
    public function setErrors(array $errors)
    {
        $this->errors = $errors;

        return $this;
    }

    /**
     * Add an error.
     *
     * @param string $error
     * @return $this
     */
    public function addError($error)
    {
        $this->errors[] = $error;

        return $this;
    }

    /**
     * Clears the errors array.
     *
     * @return $this
     */
    public function clearErrors()
    {
        $this->errors = [];

        return $this;
    }
}
