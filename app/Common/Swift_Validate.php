<?php

use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\RFCValidation;

class Swift_Validate
{
    public static function email($email)
    {
        $validator = new EmailValidator;

        return $validator->isValid($email, new RFCValidation);
    }
}
