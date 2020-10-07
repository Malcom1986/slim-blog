<?php

namespace App;

class Validator
{
    public function validate(array $user)
    {
        $errors = [];
        foreach ($user as $key => $value) {
            if (empty($user[$key])) {
                $fieldName = ucfirst($key);
                $errors[$key] = "{$fieldName} cant be blank";
            }
        }
        if ($user['password'] !== $user['passwordConfirmation']) {
            $errors['passwordConfirmation'] = 'passwords must match';
        }
        return $errors;
    }
}
