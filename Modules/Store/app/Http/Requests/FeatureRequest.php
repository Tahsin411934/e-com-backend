<?php
namespace Modules\Store\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class FeatureRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        $id = $this->route('feature')?->id ?? $this->input('feature_id');
        return ['name'=>['required','string','max:120'],'slug'=>['required','alpha_dash','max:120',Rule::unique('features','slug')->ignore($id)],'description'=>['nullable','string','max:1000'],'type'=>['required',Rule::in(['boolean','number','text'])],'is_active'=>['nullable','boolean']];
    }
}
