<?php
use Livewire\Attributes\On;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Models\RecurringTransaction;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

new #[Layout('layouts.app')] class extends Component {
    public $recurrings;
    public function mount()
    {
        $this->loadRecurrings();
    }

    #[On('recurringUpdate')]
    public function loadRecurrings()
    {
        $this->recurrings = RecurringTransaction::where('user_id', Auth::id())->get()->groupBy('frequency')->all();
    }

    public function formatShortAmount($amount)
    {
        if ($amount >= 1_000_000) {
            return number_format($amount / 1_000_000, 1) . 'M';
        } elseif ($amount >= 1_000) {
            return number_format($amount / 1_000, 1) . 'K';
        }
        return number_format($amount, 2);
    }
}; ?>

<section class="h-screen">
    <!-- Main Content with Animated Margin -->
    <!-- <div class="transition-all duration-300 ease-in-out" -->
    <!--     :class="{ 'md:mr-[17rem] lg:mr-[23rem] xl:mr-[29rem] 2xl:mr-[31rem]': detailSidebarOpen }"> -->

    <!-- No Margin -->
    <div class="transition-all duration-300 ease-in-out">

        @livewire('pages.user.containers.main-header', ['component' => 'pages.user.recurrings.header', 'header' => 'Transactions'])

        <div class="flex-1 overflow-y-auto pt-4 pb-10 px-6 bg-base-200">

            <div class="card w-full p-6 bg-base-100 shadow-xl mt-2">
                @if (count($recurrings))
                    <ul class="list bg-base-100 space-y-4">
                        @foreach ($recurrings as $frequency => $record)
                            <li
                                class="bg-base-200 text-sm font-medium py-2 px-4 mb-2 sticky top-0 z-10 backdrop-blur-sm shadow-sm flex justify-between">
                                <div class="flex items-center gap-2">
                                    <!-- <input type="checkbox" class="checkbox " /> -->
                                    <div class="flex items-center gap-2"
                                        @click="$dispatch('showRightSidebar', {operation: 'create', page: 'Transaction', component: 'pages.user.recurrings.create'}); rightSidebarOpen = true;">

                                        {{ ucfirst($frequency) }}
                                    </div>
                                </div>
                            </li>

                            @foreach ($record as $recurring)
                                @if ($recurring->transactions)
                                    <li
                                        class="group list-row hover:bg-base-200 flex items-center justify-between w-full p2-5 py-4 border border-base-200 mb-3 mx-0.5 transition-all duration-200 hover:shadow-md cursor-pointer">
                                        <!-- Red for Expense, Green for Income -->
                                        <div class="flex flex-row md:items-center w-full grow"
                                            @click="$dispatch('showSidebar', {operation: 'view', page: 'Recurring', component: 'pages.user.recurrings.view', modelId: {{ $recurring->id }}}); detailSidebarOpen = true;">
                                            <!-- Transaction Name -->
                                            <div
                                                class="w-1/3 truncate font-bold text-md mb-1.5 mr-2 text-base-content transition-colors duration-200 {{ $recurring->transactions->types->name == 'Expense' ? 'group-hover:text-secondary' : 'group-hover:text-primary' }}">
                                                {{ $recurring->transactions->name }}
                                            </div>

                                            <!-- Account Name -->
                                            <div class="w-1/3 truncate uppercase font-semibold hidden md:flex">
                                                <span
                                                    class="text-xs badge badge-lg badge-outline {{ $recurring->transactions->types->name == 'Expense' ? 'badge-secondary' : 'badge-primary' }}">
                                                    {{ $recurring->status }}
                                                </span>
                                            </div>

                                            <div class="w-1/3 flex-shrink-0 text-right grow">
                                                <span
                                                    class="text-xs uppercase font-semibold badge badge-lg truncate w-3/4 md:w-auto ">
                                                    <span class="hidden md:inline">
                                                        {{ $recurring->next_due_date }}
                                                    </span>
                                                </span>
                                            </div>
                                        </div>
                                    </li>
                                @endif
                            @endforeach
                        @endforeach
                    </ul>
                @else
                    <div class="flex flex-col items-center justify-center p-10 bg-base-200/30 ">
                        <span class="text-base-content text-lg font-medium mb-1">
                            😴 No Recurring Transactions found
                        </span>
                    </div>
                @endif
            </div>
        </div>
    </div>
    @livewire('pages.user.containers.details-sidebar', ['lazy' => true])
    @livewire('pages.user.containers.right-sidebar', ['lazy' => true])

    <!-- Add the Image Viewer Component -->
    <x-image-viewer imageUrl="" />
</section>
