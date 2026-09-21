<?php
namespace Modules\Store\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class PlanRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        $id = $this->route('plan')?->id ?? $this->input('plan_id');
        return ['name'=>['required','string','max:120'],'slug'=>['required','alpha_dash','max:120',Rule::unique('plans','slug')->ignore($id)],'description'=>['nullable','string','max:1000'],'price'=>['required','numeric','min:0','max:9999999999.99'],'currency'=>['required','string','size:3','alpha'],'duration_days'=>['nullable','integer','min:1','max:3650'],'product_limit'=>['nullable','integer','min:1'],'is_free'=>['nullable','boolean'],'is_public'=>['nullable','boolean'],'is_active'=>['nullable','boolean'],'features'=>['nullable','string','max:5000'],'feature_ids'=>['nullable','array'],'feature_ids.*'=>['integer','exists:features,id']];
    }
}
