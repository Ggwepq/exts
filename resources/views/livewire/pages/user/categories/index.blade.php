<?php

use App\Models\TransactionCategory;
use App\Models\CategoryGroup;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Volt\Component;

new class extends Component {
    public $categories;
    public $groups;

    public function mount()
    {
        $this->loadCategories();
    }

    #[On('categoryUpdate')]
    public function loadCategories()
    {
        $userId = Auth::id();

        // Load all categories with their group and type
        $allCategories = TransactionCategory::where('user_id', $userId)->with('groups', 'types')->get();

        // Group categories by group name
        $this->categories = $allCategories->groupBy(fn($cat) => optional($cat->groups)->name ?? 'None')->all();

        // Load all groups
        $allGroups = CategoryGroup::where('user_id', $userId)->where('type', 'Transaction')->get();

        // Identify group IDs that are used in the categories
        $usedGroupIds = $allCategories->pluck('group_id')->filter()->unique();

        // Add the dummy None group manually
        $dummyGroup = (object) ['id' => null, 'name' => 'None'];

        // Separate groups that are used vs unused
        $usedGroups = $allGroups->filter(fn($group) => $usedGroupIds->contains($group->id));
        $unusedGroups = $allGroups->reject(fn($group) => $usedGroupIds->contains($group->id));

        // Merge used + unused + dummy "None"
        $this->groups = $usedGroups
            ->merge($unusedGroups)
            ->map(
                fn($group) => [
                    'id' => $group->id,
                    'name' => $group->name,
                ],
            )
            ->prepend([
                'id' => null,
                'name' => 'None',
            ])
            ->values();
    }

    public function reassignCategoryGroup($categoryId, $groupId)
    {
        $userId = Auth::id();

        $category = TransactionCategory::where('id', $categoryId)->where('user_id', $userId)->firstOrFail();

        if (is_null($groupId)) {
            $category->group_id = null;
        } else {
            $group = CategoryGroup::where('id', $groupId)->where('user_id', $userId)->where('type', 'Transaction')->firstOrFail();

            $category->group_id = $group->id;
        }

        $category->save();

        $this->loadCategories(); // refresh the list
    }
}; ?>

<section>
    <!-- Yes Margin -->
    <!-- <div class="transition-all duration-300 ease-in-out" -->
    <!--     :class="{ 'md:mr-[17rem] lg:mr-[23rem] xl:mr-[27rem] 2xl:mr-[41rem]': detailSidebarOpen }"> -->
    <!-- No Margin -->
    <div class="transition-all duration-300 ease-in-out">
        @livewire('pages.user.containers.main-header', ['component' => 'pages.user.categories.header'])
        <div class="flex-1 overflow-y-auto md:pt-4 pt-4 px-6 bg-base-200 h-screen">
            <div class="card w-full p-6 bg-base-100 shadow-xl mt-2">
                @if ($categories)
                    <!-- Total Balance Banner -->

                    <ul class="list bg-base-100 space-y-4" x-data="{ draggedCategoryId: null }">
                        @foreach ($groups as $group)
                            @php
                                $groupName = $group['name'];
                                $groupId = $group['id'];
                                $record = $categories[$groupName] ?? [];
                            @endphp

                            <li class="bg-base-200/50 text-sm font-medium py-2 px-4 mb-2 sticky top-0 z-10 backdrop-blur-sm shadow-sm cursor-pointer"
                                x-data
                                @drop.prevent="$wire.call('reassignCategoryGroup', draggedCategoryId, {{ $groupId ?? 'null' }}); console.log(draggedCategoryId)"
                                @dragover.prevent @dragenter.prevent>
                                <div class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M2.25 7.125C2.25 6.504 2.754 6 3.375 6h6c.621 0 1.125.504 1.125 1.125v3.75c0 .621-.504 1.125-1.125 1.125h-6a1.125 1.125 0 0 1-1.125-1.125v-3.75ZM14.25 8.625c0-.621.504-1.125 1.125-1.125h5.25c.621 0 1.125.504 1.125 1.125v8.25c0 .621-.504 1.125-1.125 1.125h-5.25a1.125 1.125 0 0 1-1.125-1.125v-8.25ZM3.75 16.125c0-.621.504-1.125 1.125-1.125h5.25c.621 0 1.125.504 1.125 1.125v2.25c0 .621-.504 1.125-1.125 1.125h-5.25a1.125 1.125 0 0 1-1.125-1.125v-2.25Z" />
                                    </svg>
                                    {{ $groupName }}
                                </div>
                            </li>

                            @foreach ($record as $category)
                                <li class="group list-row flex hover:bg-base-200 items-center justify-between w-full px-5 py-4 border border-base-200  mb-3 mx-0.5 transition-all duration-200 hover:shadow-md cursor-pointer"
                                    @click="$dispatch('showSidebar', {operation: 'edit', page: 'Category', component: 'pages.user.categories.edit', modelId: {{ $category['id'] }}}); detailSidebarOpen = true;"
                                    draggable="true" x-data @dragstart="draggedCategoryId = {{ $category->id }}">
                                    <div class="flex items-center gap-4">
                                        <div
                                            class="p-2.5 {{ $category['type_id'] == 2 ? 'bg-secondary/10' : 'bg-primary/10' }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor"
                                                class="size-5 text-primary {{ $category['type_id'] == 2 ? 'text-secondary' : 'text-primary' }}">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M4.098 19.902a3.75 3.75 0 0 0 5.304 0l6.401-6.402M6.75 21A3.75 3.75 0 0 1 3 17.25V4.125C3 3.504 3.504 3 4.125 3h5.25c.621 0 1.125.504 1.125 1.125v4.072M6.75 21a3.75 3.75 0 0 0 3.75-3.75V8.197M6.75 21h13.125c.621 0 1.125-.504 1.125-1.125v-5.25c0-.621-.504-1.125-1.125-1.125h-4.072M10.5 8.197l2.88-2.88c.438-.439 1.15-.439 1.59 0l3.712 3.713c.44.44.44 1.152 0 1.59l-2.879 2.88M6.75 17.25h.008v.008H6.75v-.008Z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <span
                                                class="text-lg font-bold mr-2 {{ $category['type_id'] == 2 ? 'text-secondary' : 'text-primary' }}">
                                                {{ $category['name'] }}</span>
                                            <span
                                                class="badge-xs badge badge-outline {{ $category['type_id'] == 2 ? 'badge-secondary' : 'badge-primary' }}">
                                                {{ $category->types->name }}</span>
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
        @livewire('pages.user.containers.right-sidebar', ['lazy' => true])
    </div>
</section>
