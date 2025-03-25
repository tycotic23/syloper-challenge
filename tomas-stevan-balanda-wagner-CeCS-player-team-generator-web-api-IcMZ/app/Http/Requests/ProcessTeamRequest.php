<?php

namespace App\Http\Requests;

use App\Enums\PlayerPosition;
use App\Enums\PlayerSkill;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class ProcessTeamRequest extends FormRequest
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
            '*.position'=>['required',Rule::enum(PlayerPosition::class)],
            '*.mainSkill'=>['required',Rule::enum(PlayerSkill::class)],
            '*.numberOfPlayers'=>['required','numeric'],
        ];
    }

    public function messages()
    {
        return [
            '*.position'=>"Invalid value for *.position: :input",
            '*.mainSkill'=>"Invalid value for *.mainSkill: :input",
            '*.numberOfPlayers'=>"Invalid value for *.position: :input",
        ];
    }

    public function failedValidation(Validator $validator){
        $errors=$validator->errors()->first();

        throw new HttpResponseException(response([
            "message"=>$errors
        ],Response::HTTP_UNPROCESSABLE_ENTITY));
    }
}
