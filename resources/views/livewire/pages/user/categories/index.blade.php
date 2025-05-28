<?php

use App\Models\TransactionCategory;
use App\Models\CategoryGroup;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Volt\Component;
use Masmerise\Toaster\Toaster;

new class extends Component {
    public $categories;
    public $groups;
    public $refreshKey;

    public function mount()
    {
        $this->loadCategories();
    }

    #[On('categoryUpdate')]
    public function loadCategories()
    {
        $userId = Auth::id();

        // Load all categories with their group and type
        $allCategories = TransactionCategory::where('user_id', $userId)->with('groups', 'types')->orderBy('updated_at')->get();

        // Group categories by group name
        $this->categories = $allCategories->groupBy(fn($cat) => optional($cat->groups)->name ?? 'None')->all();

        // Load all groups
        $allGroups = CategoryGroup::where('user_id', $userId)->get();

        // Identify group IDs that are used in the categories
        $usedGroupIds = $allCategories->pluck('group_id')->filter()->unique();

        // Create Dummy Group
        $dummyGroup = new CategoryGroup();
        $dummyGroup->id = null;
        $dummyGroup->name = 'None';
        $dummyGroup->exists = false;

        // Separate groups that are used vs unused
        $usedGroups = $allGroups->filter(fn($group) => $usedGroupIds->contains($group->id));
        $unusedGroups = $allGroups->reject(fn($group) => $usedGroupIds->contains($group->id));

        // Merge used + unused + dummy "None"
        $this->groups = collect([$dummyGroup])
            ->merge($usedGroups)
            ->merge($unusedGroups)
            ->sortBy('name')
            ->values();

        $this->refreshKey = uniqid();
    }

    public function reassignCategoryGroup($categoryId, $groupId)
    {
        $userId = Auth::id();

        $category = TransactionCategory::where('id', $categoryId)->where('user_id', $userId)->firstOrFail();

        if (is_null($groupId)) {
            $category->group_id = null;
        } else {
            $group = CategoryGroup::where('id', $groupId)->where('user_id', $userId)->firstOrFail();

            $category->group_id = $group->id;
        }

        $category->save();

        $this->loadCategories(); // refresh the list
    }

    public function renameGroup($groupId, $newName)
    {
        if ($groupId == null) {
            Toaster::error('Not a group');
            return;
        }

        $userId = Auth::id();
        CategoryGroup::where('id', $groupId)
            ->where('user_id', $userId)
            ->update(['name' => trim($newName)]);

        Toaster::success('Category Group Updated!');

        $this->loadCategories();
    }

    public function deleteGroup($groupId)
    {
        $userId = Auth::id();
        $group = CategoryGroup::where('id', $groupId)->where('user_id', $userId);

        $group->delete();

        $this->dispatch('refresh');
        Toaster::success('Category Group Deleted!');

        $this->loadCategories();
    }
}; ?>

<section>
    <!-- Yes Margin -->
    <!-- <div class="transition-all duration-300 ease-in-out" -->
    <!--     :class="{ 'md:mr-[17rem] lg:mr-[23rem] xl:mr-[27rem] 2xl:mr-[41rem]': detailSidebarOpen }"> -->
    <!-- No Margin -->
    <div class="transition-all duration-300 ease-in-out">
        @livewire('pages.user.containers.main-header', ['component' => 'pages.user.categories.header'])
        <div class="flex-1 overflow-y-auto pt-4 pb-10 px-6 bg-base-200">
            <div class="card w-full p-6 bg-base-100 shadow-xl mt-2" wire:key="group-list-{{ $refreshKey }}">
                @if ($categories)
                    <!-- Total Balance Banner -->

                    <ul class="list bg-base-100 space-y-4" x-data="{ draggedCategoryId: null }">
                        @foreach ($groups as $group)
                            @php
                                $groupName = $group->name;
                                $groupId = $group->id;
                                $record = $categories[$groupName] ?? [];
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
                                @drop.prevent="$wire.call('reassignCategoryGroup', draggedCategoryId, {{ $groupId ?? 'null' }}); console.log(draggedCategoryId)"
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

                            @foreach ($record as $category)
                                <li class="group list-row flex hover:bg-base-200 items-center justify-between w-full px-5 py-4 border border-base-200  mb-3 mx-0.5 transition-all duration-200 hover:shadow-md cursor-pointer"
                                    @click="$dispatch('showSidebar', {operation: 'view', page: 'Category', component: 'pages.user.categories.view', modelId: {{ $category['id'] }}}); detailSidebarOpen = true;"
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

                        <li class="group gap-2 bg-base-200/50 text-sm font-medium py-2 px-4 mb-2 sticky top-0 z-10 backdrop-blur-sm shadow-sm cursor-pointer flex items-center justify-center"
                            @click="$dispatch('showRightSidebar', {operation: 'create', page: 'Group', component: 'pages.user.groups.add'}); rightSidebarOpen = true; console.log(rightSidebarOpen)">
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
                            😴 No Categories found
                        </span>
                        <button class="btn btn-sm btn-primary"
                            @click="detailSidebarOpen = true; $dispatch('showSidebar', {operation: 'create', page: 'Category', component: 'pages.user.categories.add', modelId: null})">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="size-5 mr-1">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            Add Category
                        </button>
                    </div>
                @endif
            </div>
        </div>
        @livewire('pages.user.containers.details-sidebar', ['lazy' => true])
        @livewire('pages.user.containers.right-sidebar', ['lazy' => true])
    </div>

    <x-image-viewer imageUrl="" />
</section>
