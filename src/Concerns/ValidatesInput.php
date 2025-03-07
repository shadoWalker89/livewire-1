<?php

namespace Livewire\Concerns;

use Illuminate\Mail\Message;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Validation\ValidationException;
use Livewire\ObjectPrybar;

trait ValidatesInput
{
    protected $errorBag;

    public function getErrorBag()
    {
        return $this->errorBag ?? new MessageBag;
    }

    public function setErrorBag($bag)
    {
        return $this->errorBag = $bag instanceof MessageBag
            ? $bag
            : new MessageBag($bag);
    }

    public function resetErrorBag($field = null)
    {
        if (is_null($field)) {
            $this->errorBag = new MessageBag;
        }

        $this->setErrorBag(
            Arr::except($this->errorBag->toArray(), $field)
        );
    }

    public function resetValidation($field = null)
    {
        $this->resetErrorBag($field);
    }

    public function errorBagExcept($field)
    {
        return new MessageBag(Arr::except($this->errorBag->toArray(), $field));
    }

    public function validate($rules, $messages = [], $attributes = [])
    {
        $fields = array_keys($rules);

        $result = $this->getPublicPropertiesDefinedBySubClass();

        foreach ((array) $fields as $field) {
            throw_unless(
                $this->hasProperty($field),
                new \Exception('No property found for validation: ['.$field.']')
            );

            $propertyNameFromValidationField = $this->beforeFirstDot($field);

            $result[$propertyNameFromValidationField]
                = $this->getPropertyValue($propertyNameFromValidationField);
        }

        $result = Validator::make($result, Arr::only($rules, $fields), $messages, $attributes)
            ->validate();

        // If the code made it this far, validation passed, so we can clear old failures.
        $this->resetErrorBag();

        return $result;
    }

    public function validateOnly($field, $rules, $messages = [], $attributes = [])
    {
        $result = $this->getPublicPropertiesDefinedBySubClass();

        throw_unless(
            $this->hasProperty($field),
            new \Exception('No property found for validation: ['.$field.']')
        );

        $propertyNameFromValidationField = $this->beforeFirstDot($field);

        $result[$propertyNameFromValidationField]
            = $this->getPropertyValue($propertyNameFromValidationField);

        try {
            $result = Validator::make($result, Arr::only($rules, $field), $messages, $attributes)
                ->validate();
        } catch (ValidationException $e) {
            $messages = $e->validator->getMessageBag();
            $target = new ObjectPrybar($e->validator);

            $target->setProperty(
                'messages',
                $messages->merge(
                    $this->errorBagExcept($field)
                )
            );

            throw $e;
        }

        // If the code made it this far, validation passed, so we can clear old failures.
        $this->resetErrorBag($field);

        return $result;
    }
}
