<?php

/*
 * Los mensajes de respaldo, en español y sin tecnicismos (principio IV). Cada formulario trae los
 * suyos en su FormRequest; estos salen solo si a alguna regla le falta el propio. Sin este archivo
 * la persona veria la clave interna, como «validation.required».
 */
return [
    'required' => 'Falta llenar :attribute.',
    'string' => 'Revise :attribute.',
    'integer' => 'Escriba :attribute solo con números.',
    'numeric' => 'Escriba :attribute solo con números.',
    'boolean' => 'Revise :attribute.',
    'date' => 'Escoja :attribute en el calendario.',
    'date_format' => 'Revise :attribute.',
    'after_or_equal' => 'Revise :attribute: ya pasó.',
    'before_or_equal' => 'Revise :attribute: queda fuera del plazo.',
    'in' => 'Escoja una de las opciones de :attribute.',
    'exists' => 'Revise :attribute: no aparece registrado.',
    'unique' => 'Ya hay otro registro con :attribute igual.',
    'confirmed' => 'Las dos claves no coinciden.',
    'max' => [
        'string' => ':Attribute es muy largo.',
        'numeric' => ':Attribute es demasiado alto.',
        'array' => 'Son demasiados.',
        'file' => 'El archivo es muy pesado.',
    ],
    'min' => [
        'string' => ':Attribute es muy corto.',
        'numeric' => ':Attribute es muy bajo.',
        'array' => 'Son muy pocos.',
        'file' => 'El archivo está vacío.',
    ],

    'attributes' => [
        'title' => 'el nombre',
        'type' => 'la clase de actividad',
        'date' => 'la fecha',
        'time' => 'la hora',
        'location' => 'el lugar',
        'description' => 'la descripción',
        'budget' => 'el presupuesto',
        'reason' => 'el motivo',
        'name' => 'el nombre',
        'username' => 'el usuario',
        'password' => 'la clave',
    ],
];
