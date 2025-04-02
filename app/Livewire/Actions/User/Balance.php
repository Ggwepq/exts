<?php

namespace App\Livewire\Actions\User;

use App\Models\Account;
use Illuminate\Support\Facades\Auth;
use Livewire\Form;

class Balance extends Form
{
    public function getExpense(Account $account)
    {
        return Auth::user()->transactions->where('account_id', $account->id)->where('type_id', 2)->sum('amount');
    }

    public function getIncome(Account $account)
    {
        return Auth::user()->transactions->where('account_id', $account->id)->where('type_id', 1)->sum('amount');
    }

    public function getTotalBalance(Account $account)
    {
        return $this->getIncome($account) - $this->getExpense($account);
    }

    public function get($account_id)
    {
        $income = Auth::user()->transactions->where('account_id', $account_id)->where('type_id', 1)->sum('amount');
        $expense = Auth::user()->transactions->where('account_id', $account_id)->where('type_id', 2)->sum('amount');
        return $income - $expense;
    }

    public function update($account_id)
    {
        $account = Account::find($account_id);
        
        $account->balance = $this->get($account_id);
        $account->save();
    }
}
