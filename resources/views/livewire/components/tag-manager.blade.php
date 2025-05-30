<div class="form-control w-full dropdown dropdown-start md:dropdown-center dropdown-top md:dropdown-bottom">
    <label tabindex="0" class="btn btn-md border shadow-sm w-full justify-start text-left" aria-label="Select Tag"
        :class="expense ? 'text-secondary border-secondary hover:bg-secondary/50' :
            'text-primary border-primary hover:bg-primary/50'">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
            class="size-6">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" />
        </svg>
        <span class="truncate">
            {{ count($selectedTagsObjects) > 0
                ? implode(', ', $selectedTagsObjects->pluck('name')->toArray())
                : 'Select Tags' }}
        </span>
    </label>

    <div tabindex="0"
        class="dropdown-content z-[1] mt-3 p-2 shadow-lg bg-base-100 rounded-xl w-full border border-base-200 space-y-2">

        <!-- Search Input -->
        <div class="relative">
            <input type="text" wire:model.live.debounce.300ms="searchQuery" placeholder="Search or create tags..."
                class="input input-bordered input-sm w-full pr-10" />
            <button type="button" class="btn btn-sm absolute right-0 top-0 h-full rounded-l-none"
                wire:click.prevent="createTag" :class="expense ? 'btn-secondary' : 'btn-primary'">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
            </button>
        </div>
        @error('searchQuery')
            <span class="text-error text-sm">{{ $message }}</span>
        @enderror

        <!-- Available Tag List -->
        <ul class="text-sm divide-base-300 max-h-60 overflow-y-auto">
            @foreach ($filteredTags as $tag)
                <li class="relative group">
                    <a wire:click.prevent="toggleTag({{ $tag->id }})"
                        class="flex items-center justify-between px-3 py-2 transition-all duration-200 cursor-pointer rounded-lg"
                        :class="{{ in_array($tag->id, $selectedTags) }} ?
                            (expense ?
                                'bg-gradient-to-l from-secondary/100 to-secondary/50 text-secondary-content hover:bg-secondary-focus' :
                                'bg-gradient-to-l from-primary/100 to-primary/50 text-primary-content hover:bg-primary-focus'
                                ) :
                            'hover:bg-base-200'">
                        <span class="truncate text-sm">{{ $tag->name }}</span>
                        <span
                            class="opacity-0 group-hover:opacity-100 transition-opacity duration-200 text-error hover:text-error-focus"
                            wire:click.stop="deleteTag({{ $tag->id }})" title="Delete tag">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </span>
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</div>
