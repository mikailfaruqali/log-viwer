<?php

namespace Snawbar\LogViewer\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Snawbar\LogViewer\Services\LogFileService;

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
                'regex:/^[a-zA-Z0-9\-_.]+\.log$/',
                Rule::exists('log_files')->where(function ($query) {
                    $logFileService = app(LogFileService::class);
                    return $logFileService->logFileExists($this->input('file'));
                }),
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
