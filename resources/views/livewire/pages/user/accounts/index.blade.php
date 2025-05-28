<?php
use App\Models\Account;
use App\Models\AccountCategory;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Volt\Component;

new class extends Component {
    public $accounts;
    public $groups;
    public $refreshKey;
    public $totalBalance;

    public function mount()
    {
        $this->loadAccounts();
    }

    #[On('accountUpdate')]
    public function loadAccounts()
    {
        $userId = Auth::id();

        // Load accounts with group
        $allAccounts = Account::where('user_id', $userId)->orderBy('updated_at')->get();

        // Group accounts by group name
        $this->accounts = $allAccounts->groupBy(fn($acc) => optional($acc->group)->name ?? 'None')->all();

        // Load all groups
        $allGroups = AccountCategory::where('user_id', $userId)->get();

        $usedGroupIds = $allAccounts->pluck('group_id')->filter()->unique();

        $dummyGroup = new AccountCategory();
        $dummyGroup->id = null;
        $dummyGroup->name = 'None';
        $dummyGroup->exists = false;

        $usedGroups = $allGroups->filter(fn($group) => $usedGroupIds->contains($group->id));
        $unusedGroups = $allGroups->reject(fn($group) => $usedGroupIds->contains($group->id));

        $this->groups = collect([$dummyGroup])
            ->merge($usedGroups)
            ->merge($unusedGroups)
            ->sortBy('name')
            ->values();

        $this->refreshKey = uniqid();
    }

    public function reassignAccountGroup($accountId, $groupId)
    {
        $userId = Auth::id();
        $account = Account::where('id', $accountId)->where('user_id', $userId)->firstOrFail();

        $account->group_id = $groupId ?: null;
        $account->save();

        $this->loadAccounts();
    }

    public function renameGroup($groupId, $newName)
    {
        if ($groupId === null) {
            Toaster::error('Cannot rename ungrouped');
            return;
        }

        AccountCategory::where('id', $groupId)
            ->where('user_id', Auth::id())
            ->update([
                'name' => trim($newName),
            ]);

        Toaster::success('Account Group Renamed');
        $this->loadAccounts();
    }

    public function deleteGroup($groupId)
    {
        AccountCategory::where('id', $groupId)->where('user_id', Auth::id())->delete();
        $this->dispatch('refresh');
        Toaster::success('Account Group Deleted!');
        $this->loadAccounts();
    }

    public function getTotal()
    {
        $this->totalBalance = Auth::user()->accounts->sum('balance');
    }

    public function getBalance()
    {
        $expense = Auth::user()->transactions->where('type_id', 2)->sum('amount');
        $income = Auth::user()->transactions->where('type_id', 1)->sum('amount');
        $total = $expense . '/' . $income;
    }
}; ?>
<section>
    <!-- Yes Margin -->
    <!-- Yes Margin -->
    <!-- <div class="transition-all duration-300 ease-in-out" -->
    <!--     :class="{ 'md:mr-[17rem] lg:mr-[23rem] xl:mr-[27rem] 2xl:mr-[41rem]': detailSidebarOpen }"> -->
    <!-- No Margin -->
    <div class="transition-all duration-300 ease-in-out">
        @livewire('pages.user.containers.main-header', ['component' => 'pages.user.accounts.header'])
        <div class="flex-1 overflow-y-auto pt-4 pb-10 px-6 bg-base-200">
            <div class="card w-full p-6 bg-base-100 shadow-xl mt-2" wire:key="group-list-{{ $refreshKey }}">
                @if ($accounts)
                    <!-- Total Balance Banner -->
                    <div
                        class="bg-gradient-to-r from-primary/20 to-primary/5 p-4 mb-6 shadow-sm flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="bg-primary/30 p-2.5 ">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-6 text-primary-content">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-sm font-medium text-base-content/70">Total Balance</h2>
                                <p class="text-xl font-bold text-base-content">₱{{ number_format($totalBalance) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <ul class="list bg-base-100 space-y-4" x-data="{ draggedAccountId: null }">
                        @foreach ($groups as $group)
                            @php
                                $groupName = $group->name;
                                $groupId = $group->id;
                                $record = $accounts[$groupName] ?? [];
                            @endphp

                            <li x-data="{
                                editing: false,
                                newName: @js($groupName),
                                originalName: @js($groupName),
                                startEdit() {
                                    this.originalName = this.newName;
                                    this.editing = true;
                                    this.$nextTick(() => this.$refs.input?.focus());
                                },
                                cancelEdit() {
                                    this.newName = this.originalName;
                                    this.editing = false;
                                },
                                saveEdit() { if (this.newName.trim() !== this.originalName) { $wire.call('renameGroup', '{{ $groupId }}', this.newName); } this.editing = false; }
                            }"
                                class="group bg-base-200/50 text-sm font-medium py-2 px-4 mb-2 sticky top-0 z-10 backdrop-blur-sm shadow-sm cursor-pointer flex items-center justify-between"
                                @drop.prevent="$wire.call('reassignAccountGroup', draggedAccountId, {{ $groupId ?? 'null' }});"
                                @dragover.prevent @dragenter.prevent>
                                <div class="flex items-center gap-2">
                                    <!-- icon -->
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M2.25 7.125C2.25 6.504 2.754 6 3.375 6h6c.621 0 1.125.504 1.125 1.125v3.75c0 .621-.504 1.125-1.125 1.125h-6a1.125 1.125 0 0 1-1.125-1.125v-3.75Z M14.25 8.625c0-.621.504-1.125 1.125-1.125h5.25c.621 0 1.125.504 1.125 1.125v8.25c0 .621-.504 1.125-1.125 1.125h-5.25a1.125 1.125 0 0 1-1.125-1.125v-8.25Z M3.75 16.125c0-.621.504-1.125 1.125-1.125h5.25c.621 0 1.125.504 1.125 1.125v2.25c0 .621-.504 1.125-1.125 1.125h-5.25a1.125 1.125 0 0 1-1.125-1.125v-2.25Z" />
                                    </svg>

                                    <template x-if="!editing">
                                        <span x-text="newName" class="text-base font-semibold"></span>
                                    </template>

                                    <template x-if="editing">
                                        <input type="text" x-model="newName" x-ref="input"
                                            @keydown.enter.prevent="saveEdit" @keydown.escape.prevent="cancelEdit"
                                            @blur="cancelEdit" class="input input-sm input-bordered w-48 bg-base-100" />
                                    </template>
                                    <div class="hidden "
                                        :class="newName == 'None' ? '' : 'group-hover:flex gap-2 items-center'">
                                        <button @click="startEdit" class="btn btn-xs btn-outline">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" class="size-4">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                            </svg>
                                        </button>
                                        <button wire:click="deleteGroup({{ $groupId }}); wire"
                                            class="btn btn-xs btn-error">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" class="size-4">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                            </svg>
                                        </button>
                                    </div>

                                </div>
                            </li>

                            @foreach ($record as $account)
                                <li class="group list-row hover:bg-base-200 flex items-center justify-between w-full px-5 py-4 border border-base-200  mb-3 mx-0.5 transition-all duration-200 hover:shadow-md cursor-pointer"
                                    @click="$dispatch('showSidebar', {operation: 'edit', page: 'Account', component: 'pages.user.accounts.edit', modelId: {{ $account['id'] }}}); detailSidebarOpen = true;">
                                    <div class="flex items-center gap-4">
                                        <div class="bg-primary/10 p-2.5 ">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" class="size-5 text-primary">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p
                                                class="text-lg font-bold group-hover:text-primary transition-colors duration-200">
                                                {{ $account['name'] }}</p>
                                        </div>
                                    </div>

                                    <div>
                                        <div
                                            class="text-sm uppercase font-semibold badge badge-lg whitespace-nowrap badge-primary">
                                            ₱{{ number_format($account['balance'], 2) }}
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        @endforeach

                        <li class="group gap-2 bg-base-200/50 text-sm font-medium py-2 px-4 mb-2 sticky top-0 z-10 backdrop-blur-sm shadow-sm cursor-pointer flex items-center justify-center"
                            @click="$dispatch('showRightSidebar', {operation: 'create', page: 'Group', component: 'pages.user.groups.account-add-group'}); rightSidebarOpen = true; console.log(rightSidebarOpen)">
                            <!-- icon -->
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="size-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            <span>Group</span>
                        </li>
                    </ul>
                @else
                    <div class="flex flex-col items-center justify-center p-10 bg-base-200/30 ">
                        <span class="text-base-content text-lg font-medium mb-5">
                            😴 No Accounts found
                        </span>
                        <button class="btn btn-sm btn-primary"
                            @click="detailSidebarOpen = true; $dispatch('showSidebar', {operation: 'create', page: 'Category', component: 'pages.user.categories.add', modelId: null})">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="size-5 mr-1">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            Add Account
                        </button>
                    </div>
                @endif
            </div>
        </div>
        @livewire('pages.user.containers.details-sidebar', ['lazy' => true])
        @livewire('pages.user.containers.right-sidebar', ['lazy' => true])
    </div>
</section>
