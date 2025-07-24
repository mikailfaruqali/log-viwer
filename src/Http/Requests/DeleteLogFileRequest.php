<?php

namespace Snawbar\LogViewer\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\File;

class DeleteLogFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return TRUE;
    }

    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    if (! File::exists(storage_path('logs/' . $value))) {
                        $fail("The selected log file does not exist.");
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Please select a log file to delete.',
            'file.regex' => 'Invalid log file name format.',
            'file.exists' => 'The selected log file does not exist.',
        ];
    }

    public function attributes(): array
    {
        return [
            'file' => 'log file',
        ];
    }
}
