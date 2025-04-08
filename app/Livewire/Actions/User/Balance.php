<?php

namespace App\Livewire\Actions\User;

use App\Models\Account;
use Illuminate\Support\Facades\Auth;
use Livewire\Form;

class Balance extends Form
{
    private function getExpense(Account $account)
    {
        return Auth::user()->transactions->where('account_id', $account->id)->where('type_id', 2)->sum('amount');
    }

    private function getIncome(Account $account)
    {
        return Auth::user()->transactions->where('account_id', $account->id)->where('type_id', 1)->sum('amount');
    }

    public function total(Account $account)
    {
        return $this->getIncome($account) - $this->getExpense($account);
    }

    public function get($account_id)
    {
        return Auth::user()->transactions->where('account_id', $account_id)->where('type_id', 1)->sum('amount');
    }

    public function update($account_id)
    {
        $account = Account::find($account_id);

        $account->balance = $this->get($account_id);
        $account->save();
    }
}
