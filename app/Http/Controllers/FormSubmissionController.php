<?php

namespace App\Http\Controllers;

use App\Models\CustomForm;
use App\Models\FormResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FormSubmissionController extends Controller
{
    /**
     * Submit form data.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function submit(Request $request)
    {
        // 1. Validate that the form_id (UUID) exists
        $validator = Validator::make($request->all(), [
            'form_id' => 'required|uuid|exists:custom_forms,uuid',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Form ID',
                'errors' => $validator->errors()
            ], 422);
        }

        // 2. Find the form
        $form = CustomForm::where('uuid', $request->form_id)->firstOrFail();

        // 3. Extract data (all except form_id)
        $data = $request->except(['form_id']);

        // 4. Save result (data stored as JSON string - cast removed to allow Voyager display)
        $result = FormResult::create([
            'form_id'    => $form->id,
            'data'       => is_array($data) ? json_encode($data, JSON_UNESCAPED_UNICODE) : $data,
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Form submitted successfully',
            'result_id' => $result->id
        ], 201);
    }
}
