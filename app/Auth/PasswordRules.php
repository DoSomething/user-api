<?php

namespace App\Auth;

use LangleyFoxall\LaravelNISTPasswordRules\PasswordRules as NISTPasswordRules;
use LangleyFoxall\LaravelNISTPasswordRules\Rules\BreachedPasswords;
use LangleyFoxall\LaravelNISTPasswordRules\Rules\ContextSpecificWords;
use LangleyFoxall\LaravelNISTPasswordRules\Rules\DerivativesOfContextSpecificWords;
use LangleyFoxall\LaravelNISTPasswordRules\Rules\DictionaryWords;
use LangleyFoxall\LaravelNISTPasswordRules\Rules\RepetitiveCharacters;
use LangleyFoxall\LaravelNISTPasswordRules\Rules\SequentialCharacters;

abstract class PasswordRules extends NISTPasswordRules
{
    /**
     * The base rules for setting a new password. These are used to construct
     * the "change password" rules. (We omit the "confirm" rule here, since
     * we don't ask users to confirm their password when registering.).
     */
    public static function register($username, $requireConfirmation = false)
    {
        $rules = [
            'required',
            'string',
            'min:8',
            'max:512', // We enforce this maximum to prevent long passwords from bogging down hashing.
        ];

        if ($requireConfirmation) {
            $rules[] = 'confirmed';
        }

        return array_merge($rules, [
            new SequentialCharacters(),
            new RepetitiveCharacters(),
            new DictionaryWords(),
            new ContextSpecificWords($username),
            new DerivativesOfContextSpecificWords($username),
            new BreachedPasswords(),
        ]);
    }

    public static function changePassword($username, $oldPassword = null)
    {
        return [
            ...parent::changePassword($username, $oldPassword),
            'confirmed',
        ];
    }
}
