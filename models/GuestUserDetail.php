<?php

class GuestUserDetail extends Omeka_Record_AbstractRecord
{
    public $id;

    /**
     *
     * User id.
     *
     * @var integer
     */
    public $user_id;

    /**
     * JSON-encoded set of values for the user.
     *
     * @var string
     */
    public $values;

    /**
     * Get a PHP array from the JSON-serialized layout options.
     *
     * @return array
     */
    public function getValues()
    {
        if (!empty($this->values)) {
            return json_decode($this->values, true);
        }

        return array();
    }

    /**
     * Set an key-value array of options to be JSON-encoded.
     *
     * @param array $options
     */
    public function setValues($values)
    {
        $this->values = json_encode($values);
    }
}
