<?php

namespace App\Livewire\ChartOfAccounts;

use App\Models\Account;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $typeFilter = '';

    public function render()
    {
        $accounts = Account::where('tenant_id', auth()->user()->tenant_id)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('code', 'like', '%' . $this->search . '%')
                      ->orWhere('name', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->typeFilter, function ($query) {
                $query->where('type', $this->typeFilter);
            })
            ->orderBy('code')
            ->paginate(20);

        return view('livewire.chart-of-accounts.index', [
            'accounts' => $accounts,
        ]);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingTypeFilter()
    {
        $this->resetPage();
    }
}
