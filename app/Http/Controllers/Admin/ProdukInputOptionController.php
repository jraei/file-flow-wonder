<?php

namespace App\Http\Controllers\Admin;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\ProdukInputField;
use App\Models\ProdukInputOption;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ProdukInputOptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $sort = $request->input('sort', 'id');
        $direction = $request->input('direction', 'asc');
        $fieldId = $request->input('field_id');

        // Validate the sort field to prevent SQL injection
        $allowedSortFields = ['id', 'label', 'value', 'produk_input_field_id'];
        if (!in_array($sort, $allowedSortFields)) {
            $sort = 'id';
        }


        // Start query with eager loading
        $query = ProdukInputOption::with('inputField.produk');

        // dd(ProdukInputOption::with('inputField')->get());

        // Filter by field if specified
        if ($fieldId) {
            $query->where('produk_input_field_id', $fieldId);
            $field = ProdukInputField::with('produk')->find($fieldId);
        } else {
            $field = null;
        }

        // Apply search if provided
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('label', 'like', "%{$search}%")
                    ->orWhere('value', 'like', "%{$search}%");
            });
        }

        // Apply sorting
        $query->orderBy($sort, $direction);

        // Get paginated results
        $options = $query->paginate(10)->withQueryString();

        // Get select fields for dropdown
        $fields = ProdukInputField::where('type', 'select')
            ->with('produk:id,nama')
            ->orderBy('produk_id')
            ->get();


        return Inertia::render('Admin/ProdukInputOptions', [
            'options' => $options,
            'fields' => $fields,
            'currentField' => $field,
            'filters' => [
                'search' => $search,
                'sort' => $sort,
                'direction' => $direction,
                'field_id' => $fieldId
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'produk_input_field_id' => 'required|exists:produk_input_fields,id',
            'label' => 'required|string|max:100',
            'value' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-zA-Z0-9_]+$/',
                function ($attribute, $value, $fail) use ($request) {
                    // Check for duplicate option value per field
                    $exists = ProdukInputOption::where('produk_input_field_id', $request->produk_input_field_id)
                        ->where('value', $value)
                        ->exists();

                    if ($exists) {
                        $fail('The option value already exists for this field.');
                    }
                },
            ],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Check if the field is actually a select type
        $field = ProdukInputField::find($request->produk_input_field_id);
        if (!$field || $field->type !== 'select') {
            return back()->withErrors(['type' => 'Options can only be added to select fields'])->withInput();
        }

        // Create the option
        $option = ProdukInputOption::create($request->all());

        return redirect()->route('admin.produk-input-options.index', ['field_id' => $request->produk_input_field_id])
            ->with('status', ['type' => 'success', 'action' => 'Success', 'text' => 'Option has been added!']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $option = ProdukInputOption::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'produk_input_field_id' => 'required|exists:produk_input_fields,id',
            'label' => 'required|string|max:100',
            'value' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-zA-Z0-9_]+$/',
                function ($attribute, $value, $fail) use ($request, $option) {
                    // Check for duplicate option value per field (excluding current option)
                    $exists = ProdukInputOption::where('produk_input_field_id', $request->produk_input_field_id)
                        ->where('value', $value)
                        ->where('id', '!=', $option->id)
                        ->exists();

                    if ($exists) {
                        $fail('The option value already exists for this field.');
                    }
                },
            ],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $option->update($request->all());

        return redirect()->route('admin.produk-input-options.index', ['field_id' => $request->produk_input_field_id])
            ->with('status', ['type' => 'success', 'action' => 'Success', 'text' => 'Option has been updated!']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $option = ProdukInputOption::findOrFail($id);
        $fieldId = $option->produk_input_field_id;

        $option->delete();

        return redirect()->route('admin.produk-input-options.index', ['field_id' => $fieldId])
            ->with('status', ['type' => 'success', 'action' => 'Success', 'text' => 'Option has been deleted!']);
    }

    /**
     * Bulk store options for a field.
     */
    public function bulkStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'produk_input_field_id' => 'required|exists:produk_input_fields,id',
            'options' => 'required|array|min:1',
            'options.*.label' => 'required|string|max:100',
            'options.*.value' => 'required|string|max:100|regex:/^[a-zA-Z0-9_]+$/',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check if the field is actually a select type
        $field = ProdukInputField::find($request->produk_input_field_id);
        if (!$field || $field->type !== 'select') {
            return response()->json(['errors' => ['type' => ['Options can only be added to select fields']]], 422);
        }

        // Create options and handle duplicates
        $created = 0;
        $duplicates = 0;

        foreach ($request->options as $optionData) {
            $exists = ProdukInputOption::where('produk_input_field_id', $request->produk_input_field_id)
                ->where('value', $optionData['value'])
                ->exists();

            if (!$exists) {
                ProdukInputOption::create([
                    'produk_input_field_id' => $request->produk_input_field_id,
                    'label' => $optionData['label'],
                    'value' => $optionData['value']
                ]);
                $created++;
            } else {
                $duplicates++;
            }
        }

        return response()->json([
            'message' => "{$created} options added successfully" . ($duplicates > 0 ? ", {$duplicates} duplicates skipped" : "")
        ]);
    }
}
