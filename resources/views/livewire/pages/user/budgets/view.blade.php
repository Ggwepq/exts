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

    public $category;

    public function mount($modelId)
    {
        $this->category = TransactionCategory::findOrFail($modelId);
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
        $allGroups = CategoryGroup::where('user_id', $userId)->where('type', 'Transaction')->get();

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
            $group = CategoryGroup::where('id', $groupId)->where('user_id', $userId)->where('type', 'Transaction')->firstOrFail();

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
    <div x-show="open" x-transition class="mt-4 space-y-2 text-sm bg-base-200 p-4 rounded-lg">
        <div class="flex justify-between">
            <span class="text-base-content/70">Budget Limit</span>
            <span class="font-medium">₱{{ number_format($category->budgets->limit_amount ?? 0, 2) }}</span>
        </div>
        <div class="flex justify-between">
            <span class="text-base-content/70">Total Spent</span>
            <span
                class="font-medium text-error">₱{{ number_format($category->transactions->where('type_id', '2')->sum('amount') ?? 0, 2) }}</span>
        </div>
        <div class="flex justify-between">
            <span class="text-base-content/70">Remaining</span>
            <span
                class="font-medium text-success">₱{{ number_format(($category->budgets->limit_amount ?? 0) - ($category->transactions->where('type_id', '2')->sum('amount') ?? 0), 2) }}</span>
        </div>
    </div>

</section>
