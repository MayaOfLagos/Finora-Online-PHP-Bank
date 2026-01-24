<?php

namespace App\Filament\Helpers;

use App\Helpers\Countries;
use App\Models\BeneficiaryFieldTemplate;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Collection;

class BeneficiaryFieldMapper
{
    /**
     * Map beneficiary field templates to Filament form components
     */
    public static function mapToFilamentComponents(string $transferType): array
    {
        $fields = BeneficiaryFieldTemplate::forTransferType($transferType);

        return $fields->map(function (BeneficiaryFieldTemplate $field) {
            return static::mapFieldToComponent($field);
        })->all();
    }

    /**
     * Map single field template to Filament component
     */
    protected static function mapFieldToComponent(BeneficiaryFieldTemplate $field)
    {
        $component = match ($field->field_type) {
            'text' => TextInput::make($field->field_key)
                ->label($field->field_label)
                ->placeholder($field->placeholder)
                ->maxLength(255),

            'textarea' => Textarea::make($field->field_key)
                ->label($field->field_label)
                ->placeholder($field->placeholder)
                ->rows(3),

            'select' => Select::make($field->field_key)
                ->label($field->field_label)
                ->options($field->options ?? [])
                ->placeholder($field->placeholder)
                ->searchable(),

            'country' => Select::make($field->field_key)
                ->label($field->field_label)
                ->options(Countries::forSelect())
                ->placeholder($field->placeholder ?? 'Select country')
                ->searchable(),

            default => TextInput::make($field->field_key)
                ->label($field->field_label)
                ->placeholder($field->placeholder),
        };

        // Apply required rule
        if ($field->is_required) {
            $component->required();
        }

        // Apply helper text
        if ($field->helper_text) {
            $component->helperText($field->helper_text);
        }

        return $component;
    }

    /**
     * Get validation rules for dynamic fields
     */
    public static function getValidationRules(string $transferType): array
    {
        $fields = BeneficiaryFieldTemplate::forTransferType($transferType);
        $rules = [];

        foreach ($fields as $field) {
            $fieldRules = [];

            if ($field->is_required) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }

            // Add type-specific rules
            $fieldRules = array_merge($fieldRules, static::getTypeSpecificRules($field->field_type));

            $rules[$field->field_key] = implode('|', $fieldRules);
        }

        return $rules;
    }

    /**
     * Get type-specific validation rules
     */
    protected static function getTypeSpecificRules(string $fieldType): array
    {
        return match ($fieldType) {
            'text' => ['string', 'max:255'],
            'textarea' => ['string', 'max:1000'],
            'select' => ['string'],
            'country' => ['string', 'size:2'],
            default => ['string'],
        };
    }

    /**
     * Extract beneficiary data from request
     */
    public static function extractBeneficiaryData(array $data, string $transferType): array
    {
        $fields = BeneficiaryFieldTemplate::forTransferType($transferType);
        $beneficiaryData = [];

        foreach ($fields as $field) {
            if (isset($data[$field->field_key])) {
                $beneficiaryData[$field->field_key] = $data[$field->field_key];
            }
        }

        return $beneficiaryData;
    }
}
