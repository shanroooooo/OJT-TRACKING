<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReviewTimeLogRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = auth()->user();
        $log = $this->route('log');
        $studentProfile = $log->studentProfile;

        // Check if user is supervisor and has access to this student
        return $user->role === 'supervisor' && (
            $studentProfile->supervisor_contact === $user->email ||
            strpos($studentProfile->supervisor_name, $user->name) !== false
        );
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => 'required|in:approved,rejected',
            'review_notes' => 'nullable|string|max:1000',
            'adjusted_hours' => 'nullable|numeric|min:0|max:24',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'status.required' => 'Please select approve or reject.',
            'status.in' => 'Invalid status. Must be approved or rejected.',
            'adjusted_hours.min' => 'Adjusted hours cannot be negative.',
            'adjusted_hours.max' => 'Adjusted hours cannot exceed 24 hours.',
            'review_notes.max' => 'Review notes cannot exceed 1000 characters.',
        ];
    }
}
