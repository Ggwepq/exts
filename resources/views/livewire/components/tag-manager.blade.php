<div class="tag-manager w-full space-y-4">
    <!-- Combined Search/Create Input -->

    <label class="label py-0 mb-2">
        <span class="label-text text-sm">Tags</span>
    </label>
    <div class="form-control">
        <div class="relative w-full">
            <input type="text" wire:model.live.debounce.300ms="searchQuery" placeholder="Search or create tags..."
                class="input input-bordered input-sm w-full pr-12" wire:keydown.enter.prevent="createTag" />
            <button type="button" class="btn btn-primary btn-sm absolute right-0 top-0 h-full rounded-l-none"
                wire:click.prevent="createTag">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
            </button>
        </div>
        @error('searchQuery')
            <span class="text-error text-sm">{{ $message }}</span>
        @enderror
    </div>

    <!-- Selected Tags Display -->
    @if (count($selectedTagsObjects) > 0)
        <div class="selected-tags ">
            <div class="flex flex-wrap gap-1.5 mt-1">
                @foreach ($selectedTagsObjects as $tag)
                    <div class="badge badge-sm  gap-1.5 h-6 border "
                        :class="!expense ? 'bg-primary hover:bg-primary-focus text-primary-content border-primary-focus' :
                            'bg-secondary hover:bg-secondary-focus text-secondary-content border-secondary-focus'">
                        {{ $tag->name }}
                        <button type="button" wire:click.prevent="removeTag({{ $tag->id }})"
                            class="hover:text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
        <hr />
    @endif

    <!-- Available Tags -->
    @if (count($filteredTags) > 0)
        <div class="available-tags">
            <div class="flex flex-wrap gap-1.5 mt-1">
                @foreach ($filteredTags as $tag)
                    <div class="flex items-center gap-0.5 group">
                        <button type="button" wire:click.prevent="toggleTag({{ $tag->id }})"
                            class="badge badge-sm h-6 border {{ in_array($tag->id, $selectedTags)
                                ? 'bg-primary hover:bg-primary-focus text-primary-content border-primary-focus'
                                : 'bg-base-200 hover:bg-base-300 text-base-content border-base-300' }}">
                            {{ $tag->name }}
                        </button>
                        <button type="button" wire:click="deleteTag({{ $tag->id }})"
                            wire:confirm="Are you sure you want to delete this tag?"
                            class="text-error hover:text-error-focus opacity-0 group-hover:opacity-100 group-hover:flex hidden transition-all duration-200"
                            title="Delete tag">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
