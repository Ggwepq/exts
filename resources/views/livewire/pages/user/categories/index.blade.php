<?php
use App\Models\TransactionCategory;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Volt\Component;

new class extends Component {
    public $categories;

    public function mount()
    {
        $this->loadCategories();
    }

    #[On('categoryUpdate')]
    public function loadCategories()
    {
        $user = TransactionCategory::where('user_id', Auth::id());

        $this->categories = $user
            ->with('categories')
            ->orderByRaw('group_id IS NOT NULL') // "null" comes first
            ->orderBy(function ($query) {
                $query->select('created_at')->from('category_groups')->whereColumn('category_groups.id', 'transaction_categories.group_id')->limit(1);
            })
            ->get()
            ->groupBy(function ($category) {
                $name = $category->categories ? $category->categories->name : 'None';

                return $name;
            })
            ->toArray();
    }
}; ?>

<section>
    <div class="transition-all duration-300 ease-in-out"
        :class="{ 'md:mr-[17rem] lg:mr-[23rem] xl:mr-[27rem] 2xl:mr-[41rem]': detailSidebarOpen }">
        @livewire('pages.user.containers.main-header', ['component' => 'pages.user.categories.header'])
        <div class="flex-1 overflow-y-auto md:pt-4 pt-4 px-6 bg-base-200 h-screen">
            <div class="card w-full p-6 bg-base-100 shadow-xl mt-2">
                @if ($categories)
                    <!-- Total Balance Banner -->

                    <ul class="list bg-base-100 space-y-4">
                        @foreach ($categories as $categoryName => $records)
                            <li @click="Toaster.success('CLiekceddasdf')"
                                class="bg-base-200/50 text-sm font-medium py-2 px-4 mb-2 sticky top-0 z-10 backdrop-blur-sm shadow-sm">
                                <div class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="size-4 text-base-content/70">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                    </svg>
                                    {{ $categoryName }}
                                </div>
                            </li>

                            @foreach ($records as $category)
                                <li class="group list-row hover:bg-base-200 flex items-center justify-between w-full px-5 py-4 border border-base-200  mb-3 mx-0.5 transition-all duration-200 hover:shadow-md cursor-pointer"
                                    @click="$dispatch('showSidebar', {operation: 'edit', page: 'Category', component: 'pages.user.categories.edit', modelId: {{ $category['id'] }}}); detailSidebarOpen = true;">
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
                                                {{ $category['name'] }}</p>
                                        </div>
                                    </div>

                                </li>
                            @endforeach
                        @endforeach
                    </ul>
                @else
                    <div class="flex flex-col items-center justify-center p-10 bg-base-200/30 ">
                        <span class="text-base-content text-lg font-medium mb-1">
                            😴 No transactions found
                        </span>
                        <button class="btn btn-sm btn-primary"
                            @click="detailSidebarOpen = true; $dispatch('showSidebar', {operation: 'create', page: 'Category', component: 'pages.user.categories.add', modelId: null})">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="size-5 mr-1">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            Add Your First Account
                        </button>
                    </div>
                @endif
            </div>
        </div>
        @livewire('pages.user.containers.details-sidebar', ['lazy' => true])
    </div>
</section>
