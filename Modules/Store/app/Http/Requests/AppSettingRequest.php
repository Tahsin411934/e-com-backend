<?php

namespace Modules\Store\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AppSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $settingId = $this->route('app_setting') ?? $this->input('setting_id');
        $uniqueKey = 'unique:app_settings,setting_key'.($settingId ? ','.$settingId : '');

        return [
            'scope_type' => 'required|in:global,store,user',
            'scope_id' => 'required|integer|min:0',
            'setting_key' => 'required|string|max:120|'.$uniqueKey,
            'setting_value' => 'required',
            'is_public' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'scope_type.required' => 'Scope type is required.',
            'scope_type.in' => 'Scope type must be global, store, or user.',
            'setting_key.required' => 'Setting key is required.',
            'setting_key.unique' => 'Setting key must be unique within the scope.',
            'setting_value.required' => 'Setting value is required.',
        ];
    }
}
