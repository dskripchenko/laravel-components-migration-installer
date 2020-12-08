<?php


namespace Dskripchenko\LaravelCMI\Components;


use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BaseCommand extends Command
{
    protected $componentName;

    /**
     * @param $question
     * @param $rules
     * @return mixed
     */
    protected function askValid($question, $rules)
    {
        $value = $this->ask($question);
        if ($message = $this->validateInput($rules, $value)) {
            $this->error($message);
            return $this->askValid($question, $rules);
        }

        return $value;
    }


    /**
     * @param $rules
     * @param $value
     * @return string|null
     */
    protected function validateInput($rules, $value)
    {
        $validator = Validator::make(
            [
                'field' => $value
            ],
            [
                'field' => $rules
            ]
        );

        return $validator->fails()
            ? $validator->errors()->first('field')
            : null;
    }

    /**
     * @param string $message
     * @return string
     */
    protected function getNewMigrationName($message = 'Enter migration name')
    {
        $name = $this->askValid(
            trans($message),
            [
                'required',
                'min:3',
                'regex:/^[a-zA-Z_]+$/i',
                function ($attribute, $value, $fail) {
                    $className = Str::camel($value);
                    if (class_exists($className)) {
                        $errorMessage = "{$className} " . trans("already exists");
                        $fail($errorMessage);
                    }
                }
            ]
        );
        return Str::camel($name);
    }
}
