<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssociateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $associateId = $this->route('id') ?? $this->route('associate') ?? $this->input('id');
        $isUpdate = in_array($this->route()->getName(), ['associate-panel.associate-update', 'dusra-route-name']);

        return [
            'sponsor_id' => ['required', 'string', 'max:50'],
            'under_place_id' => ['required', 'string', 'max:50'],
            'direction' => ['required', 'in:left,right'],
            // 'rank_id' => ['required', 'exists:designation_ranks,id'],
            'associate_name' => ['required', 'string', 'min:3', 'max:100'],
            'gender' => ['required', 'in:male,female'],
            'title' => ['required', 'string', 'max:20'],
            'father_name' => ['required', 'string', 'min:3', 'max:100'],
            'dob' => ['required', 'date', 'before:today'],
            'mobile_number' => [
                'required',
                'digits:10',
                'unique:associates,mobile_number,'.$associateId,
            ],
            'email' => [
                'nullable',
                'email',
                'unique:associates,email,'.$associateId,
            ],
            'address' => ['required', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:50'],
            'state' => ['required', 'string', 'max:50'],
            'pancard_number' => $isUpdate
                ? ['nullable']
                : ['nullable', 'regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/', 'unique:associates,pancard_number,'.$associateId],
            'aadhar_number' => $isUpdate
                ? ['nullable']
                : ['nullable', 'regex:/^[0-9]{12}$/', 'unique:associates,aadhar_number,'.$associateId],

            'bank_name' => ['nullable', 'string', 'max:100'],
            'account_number' => ['nullable', 'regex:/^[0-9]{9,18}$/'],
            'ifsc_code' => ['nullable', 'regex:/^[A-Z]{4}0[A-Z0-9]{6}$/'],
            'account_holder_name' => ['nullable', 'string', 'max:100'],
            'nominee_name' => ['nullable', 'string', 'max:100'],
            'nominee_relation' => ['nullable', 'string', 'max:50'],
            'nominee_age' => ['nullable', 'integer', 'min:1', 'max:120'],
            'joining_date' => ['nullable', 'date'],
            'photo' => [$associateId ? 'nullable' : 'nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'id_proof_photo' => [$associateId ? 'nullable' : 'nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'pancard_photo' => [$associateId ? 'nullable' : 'nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'bank_passbook' => [$associateId ? 'nullable' : 'nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [

            'sponsor_id.required' => 'Please select sponsor.',

            'under_place_id.required' => 'Under place ID is required.',
            'direction.required' => 'Please select direction.',
            // 'rank_id.required' => 'Please select rank.',
            // 'rank_id.exists' => 'Selected rank is invalid.',

            'associate_name.required' => 'Associate name is required.',

            'gender.required' => 'Please select gender.',

            'title.required' => 'Title is required.',

            'father_name.required' => 'Father name is required.',

            'dob.required' => 'Date of birth is required.',
            'dob.before' => 'Date of birth must be before today.',

            'address.required' => 'Address is required.',

            'city.required' => 'City is required.',

            'state.required' => 'State is required.',
            'mobile_number.required' => 'Mobile number is required.',
            'mobile_number.digits' => 'Mobile number must be exactly 10 digits.',
            'mobile_number.unique' => 'This mobile number is already registered.',

            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already registered.',

            // PAN
            'pancard_number.required' => 'PAN number is required.',
            'pancard_number.regex' => 'Please enter a valid PAN number (Example: ABCDE1234F).',
            'pancard_number.unique' => 'This PAN number already exists.',

            // Aadhaar
            'aadhar_number.required' => 'Aadhaar number is required.',
            'aadhar_number.regex' => 'Aadhaar number must be exactly 12 digits.',
            'aadhar_number.unique' => 'This Aadhaar number already exists.',

            // Bank
            'bank_name.required' => 'Bank name is required.',

            'account_number.required' => 'Account number is required.',
            'account_number.regex' => 'Please enter a valid bank account number.',

            'ifsc_code.required' => 'IFSC code is required.',
            'ifsc_code.regex' => 'Please enter a valid IFSC code (Example: SBIN0001234).',

            'account_holder_name.required' => 'Account holder name is required.',

            // Files
            'photo.required' => 'Photo is required.',
            'photo.image' => 'Photo must be an image file.',

            'id_proof_photo.required' => 'ID proof image is required.',
            'id_proof_photo.image' => 'ID proof must be an image file.',
            'pancard_photo.required' => 'ID proof image is required.',
            'pancard_photo.image' => 'ID proof must be an image file.',
            'bank_passbook.required' => 'Bank passbook image is required.',
            'bank_passbook.image' => 'Bank passbook must be an image file.',

        ];
    }
}