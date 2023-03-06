<?php

namespace App\Http\Controllers;

use App\Models\IncomeItem;
use Illuminate\Http\Request;

class IncomeItemController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        request()->validate([
            'project_id' => ['required', 'exists:projects,id']
        ]);
        $tests = IncomeItem::where('project_id', request()->project_id)
            ->withCount('tests')
            ->get();

        return response($tests, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'max:255'],
            'description' => ['nullable', 'max:255'],
            'array_table' => ['required', 'between:6,6', 'array'],
            'project_id' => ['required', 'exists:projects,id'],
        ]);

        IncomeItem::create($data);

        return response(['message' => 'Success'], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\IncomeItem  $incomeItem
     * @return \Illuminate\Http\Response
     */
    public function show(IncomeItem $incomeItem)
    {
        return response($incomeItem, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\IncomeItem  $incomeItem
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, IncomeItem $incomeItem)
    {
        $request->validate([
            'name' => ['required', 'max:255'],
            'description' => ['nullable', 'max:255'],
            'array_table' => ['required', 'between:6,6', 'array'],
            'project_id' => ['required', 'exists:projects,id'],
        ]);

        $incomeItem->update([
            'name' => $request->name,
            'description' => $request->description,
            'array_table' => $request->array_table,
        ]);

        return response(['message' => 'Success'], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\IncomeItem  $incomeItem
     * @return \Illuminate\Http\Response
     */
    public function destroy(IncomeItem $incomeItem)
    {
        $incomeItem->delete();

        return response(['message' => 'Success'], 200);
    }
}
