<?php
use App\Models\AccountCategory;
use Livewire\Volt\Component;

new class extends Component {
    public $accountCategories;

    public function mount()
    {
        $this->accountCategories = auth()->user()->accountCategories;
        // dd($this->accountCategories);
    }
}; ?>

<main class="flex-1 overflow-y-auto md:pt-4 pt-4 px-6  bg-base-200">

    <div class="card w-full p-6 bg-base-100 shadow-xl mt-2">
        <ul class="list bg-base-100 rounded-box shadow-md">

            @if (count($accountCategories))
                @foreach ($accountCategories as $accountCategory)
                    <li class="p-4 pb-2 text-xs opacity-60 tracking-wide">{{ $accountCategory->name }}</li>

                    @foreach ($accountCategory->accounts as $account)
                        <li class="list-row">
                            <div>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-10">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                                </svg>
                            </div>
                            <div class="text-lg font-bold">{{ $account->name }}
                            </div>

                            <div>
                                <div class="text-sm uppercase font-semibold badge badge-outline badge-primary">
                                    {{ $account->balance }}
                                </div>
                            </div>
                        </li>
                    @endforeach
                @endforeach
            @else
                <div class="flex flex-row justify-center">
                    😪 No transactions
                </div>
            @endif


        </ul>
        <!-- <div class="h-16"></div> -->
</main>
