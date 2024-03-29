<?php

namespace App\Http\Controllers;

use App\Models\BalanceTest;
use App\Models\IncomeTest;
use App\Models\ResultToc;
use App\Rules\NatureControlRule;
use Illuminate\Http\Request;

class ResultTocController extends Controller
{
    public function errors(Request $request)
    {
        $this->authorize('test-create');

        $error = null;
        if($request->balance_test_id){
            $error = BalanceTest::where('id', $request->balance_test_id)
                ->with('comments')
                ->select('id',
                    'first_error',
                    'first_size',
                    'second_error',
                    'second_size',
                    'nature_control_id',
                )
                ->first();
        }
        if($request->income_test_id){
            $error = IncomeTest::where('id', $request->income_test_id)
                ->with('comments')
                ->select(
                    'id',
                    'first_error',
                    'first_size',
                    'second_error',
                    'second_size',
                    'nature_control_id',
                )
                ->first();
        }

        return response($error, 200);
    }

    public function balanceError(Request $request)
    {
        $this->authorize('test-create');

        $request->validate([
            'first_error' => ['required_if:second_error,=,null'],
            'second_error' => ['required_if:first_error,=,null'],
            'balance_test_id' => ['required', 'exists:balance_tests,id'],
            'nature_control_id' => [
                'exists:nature_controls,id',
                new NatureControlRule(natureControlID: $request->nature_control_id, balanceID: $request->balance_test_id)]
        ]);

        $balanceTest = BalanceTest::where('id', $request->balance_test_id)->first();
        if ($request->first_error !== null){
            $balanceTest->update([
                'first_error' => $request->first_error
            ]);
        }else {
            $balanceTest->update([
                'second_error' => $request->second_error
            ]);
        }

        return response(['message' => 'Success'], 200);
    }

    public function incomeError(Request $request)
    {
        $this->authorize('test-create');

        $request->validate([
            'first_error' => ['required_if:second_error,=,null'],
            'second_error' => ['required_if:first_error,=,null'],
            'income_test_id' => ['required', 'exists:income_tests,id'],
            'nature_control_id' => [
                'exists:nature_controls,id',
                new NatureControlRule(natureControlID: $request->nature_control_id, incomeID: $request->income_test_id)]
            ]);

        $incomeTest = IncomeTest::where('id', $request->income_test_id)->first();

        if ($request->first_error !== null){
                $incomeTest->update([
                    'first_error' => $request->first_error
                ]);
            }else {
                $incomeTest->update([
                    'second_error' => $request->second_error
                ]);
            }

        return response(['message' => 'Success'], 200);
    }

    public function incomeComments(Request $request)
    {
        $request->validate([
            'income_test_id' => ['required', 'exists:income_tests,id']
        ]);

        $resultToc = ResultToc::where('income_test_id', $request->income_test_id)->first();
        if($resultToc){
            $resultToc->update([
                'first_comment' => $request->first_comment,
                'second_comment' => $request->second_comment,
                'first_link' => $request->first_link,
                'second_link' => $request->second_link
            ]);
        }else{
            ResultToc::create([
                'income_test_id' => $request->income_test_id,
                'first_comment' => $request->first_comment,
                'second_comment' => $request->second_comment,
                'first_link' => $request->first_link,
                'second_link' => $request->second_link
            ]);
        }

        return response(['message' => 'Success'], 200);
    }

    public function balanceComments(Request $request)
    {
        $request->validate([
            'balance_test_id' => ['required', 'exists:balance_tests,id']
        ]);

        $resultToc = ResultToc::where('balance_test_id', $request->balance_test_id)->first();
        if($resultToc){
            $resultToc->update([
                'first_comment' => $request->first_comment,
                'second_comment' => $request->second_comment,
                'first_link' => $request->first_link,
                'second_link' => $request->second_link
            ]);
        }else{
            ResultToc::create([
                'balance_test_id' => $request->balance_test_id,
                'first_comment' => $request->first_comment,
                'second_comment' => $request->second_comment,
                'first_link' => $request->first_link,
                'second_link' => $request->second_link
            ]);
        }

        return response(['message' => 'Success'], 200);
    }
}
