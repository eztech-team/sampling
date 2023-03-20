<?php

namespace App\Http\Controllers;

use App\Models\BalanceItem;
use App\Models\Project;
use Illuminate\Http\Request;

class BalanceItemController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->authorize('item-index');

        request()->validate([
            'project_id' => ['required', 'exists:projects,id']
        ]);
        $tests = BalanceItem::where('project_id', request()->project_id)
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
        $this->authorize('item-create');

        $data = $request->validate([
            'name' => ['required', 'max:255'],
            'description' => ['nullable', 'max:255'],
            'array_table' => ['required', 'between:6,6', 'array'],
            'project_id' => ['required', 'exists:projects,id'],
        ]);

        BalanceItem::create($data);

        return response(['message' => 'Success'], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\BalanceItem  $balanceItem
     * @return \Illuminate\Http\Response
     */
    public function show(BalanceItem $balanceItem)
    {
        $this->authorize('item-edit', $balanceItem);

        return response($balanceItem, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\BalanceItem  $balanceItem
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, BalanceItem $balanceItem)
    {
        $this->authorize('item-edit', $balanceItem);

        $request->validate([
            'name' => ['required', 'max:255'],
            'description' => ['nullable', 'max:255'],
            'array_table' => ['required', 'between:6,6', 'array'],
            'project_id' => ['required', 'exists:projects,id'],
        ]);

        $balanceItem->update([
            'name' => $request->name,
            'description' => $request->description,
            'array_table' => $request->array_table,
        ]);

        return response(['message' => 'Success'], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\BalanceItem  $balanceItem
     * @return \Illuminate\Http\Response
     */
    public function destroy(BalanceItem $balanceItem)
    {
        $this->authorize('item-delete');

        $balanceItem->delete();

        return response(['message' => 'Success'], 200);
    }
}
