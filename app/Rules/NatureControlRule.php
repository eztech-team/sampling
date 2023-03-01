<?php

namespace App\Rules;

use App\Models\BalanceTest;
use App\Models\NatureControl;
use Illuminate\Contracts\Validation\Rule;

class NatureControlRule implements Rule
{

    protected $natureControlID;
    protected $balanceID;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($natureControlID, $balanceID)
    {
        $this->natureControlID = $natureControlID;
        $this->balanceID = $balanceID;
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
        $balanceTest = BalanceTest::find($this->balanceID);

        return $nature->second_error == 1 && $balanceTest->second_size == null or $balanceTest->second_size == 0;
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
