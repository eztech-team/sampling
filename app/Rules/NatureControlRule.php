<?php

namespace App\Rules;

use App\Models\BalanceTest;
use App\Models\IncomeTest;
use App\Models\NatureControl;
use Illuminate\Contracts\Validation\Rule;

class NatureControlRule implements Rule
{

    protected $natureControlID;
    protected $balanceID;
    protected $incomeID;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($natureControlID, $balanceID = null, $incomeID = null)
    {
        $this->natureControlID = $natureControlID;
        $this->balanceID = $balanceID;
        $this->incomeID = $incomeID;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $nature = NatureControl::find($this->natureControlID);
        if($this->balanceID) {
            $test = BalanceTest::find($this->balanceID);
        }
        if($this->incomeID) {
            $test = IncomeTest::find($this->incomeID);
        }

        return $nature->first_error == 0 or (($nature->first_error > $test->first_error) && $test->second_size == null);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'We can not calculate balance';
    }
}
