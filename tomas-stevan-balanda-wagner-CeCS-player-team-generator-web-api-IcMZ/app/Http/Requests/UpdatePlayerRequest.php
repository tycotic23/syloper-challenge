<?php

namespace App\Http\Requests;

use App\Enums\PlayerPosition;
use App\Enums\PlayerSkill;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class UpdatePlayerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name'=>['sometimes'],
            //'position'=>['sometimes',Rule::enum(PlayerPosition::class)],
            'playerSkills'=>['sometimes'],
            'playerSkills.*.skill'=>['required',Rule::enum(PlayerSkill::class)],
            'playerSkills.*.value'=>['required','numeric'],
            'playerSkills.*.id'=>['sometimes','numeric','nullable'],
        ];
    }

    public function messages()
    {
        return [
            'name'=>"Invalid value for name: :input",
            'position'=>"Invalid value for position: :input",
            'playerSkills'=>"Invalid value for playerSkills: :input",
            'playerSkills.*.skill'=>"Invalid value for playerSkills.*.skill: :input",
            'playerSkills.*.id'=>"Invalid value for playerSkills.*.id: :input",
            'playerSkills.*.value'=>"Invalid value for playerSkills.*.value: :input"
        ];
    }

    public function failedValidation(Validator $validator){
        $errors=$validator->errors()->first();

        throw new HttpResponseException(response([
            "message"=>$errors
        ],Response::HTTP_UNPROCESSABLE_ENTITY));
    }
}
