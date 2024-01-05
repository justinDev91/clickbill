<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 *
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class EmailExists extends Constraint
{
    public $message = 'Un compte est déjà existant pour l\'email "{{ email }}". Si vous ne vous connaissez pas votre mot de passe, veuillez le réinitialiser.';
}
