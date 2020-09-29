<?php

namespace App;

class Validator
{
    public function validate(array $user)
    {
        $errors = [];
        foreach ($user as $key => $value) {
            if (empty($user[$key])) {
                $errors[$key] = 'Can not be blank';
            }
        }
        if ($user['password'] !== $user['passwordConfirmation']) {
            $errors['passwordConfirmation'] = 'passwords must match';
        }
        return $errors;
    }
}
