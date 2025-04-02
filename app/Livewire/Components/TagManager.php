<?php

namespace App\Livewire\Components;

use App\Models\Tag;
use Livewire\Component;
use Livewire\Attributes\Validate;
use Illuminate\Support\Collection;

class TagManager extends Component
{
    public $selectedTags = [];
    public $availableTags;
    public $searchQuery = '';
    public $isEditing = false;

    #[Validate('required|string|min:2|max:50')]
    public $newTagName = '';

    public function mount($initialSelectedTags = [], $isEditing = false)
    {
        $this->selectedTags = $initialSelectedTags;
        $this->isEditing = $isEditing;
        $this->loadAvailableTags();
    }

    public function loadAvailableTags()
    {
        $this->availableTags = Tag::orderBy('name')->get();
    }

    public function getFilteredTagsProperty(): Collection
    {
        if (empty($this->searchQuery)) {
            return $this->availableTags;
        }

        return $this->availableTags->filter(function ($tag) {
            return stripos($tag->name, $this->searchQuery) !== false;
        });
    }

    public function getSelectedTagsObjectsProperty()
    {
        return Tag::whereIn('id', $this->selectedTags)->get();
    }

    public function toggleTag($tagId)
    {
        if (in_array($tagId, $this->selectedTags)) {
            $this->removeTag($tagId);
        } else {
            $this->selectedTags[] = $tagId;
            $this->updateParentTags();
        }
    }

    public function removeTag($tagId)
    {
        $this->selectedTags = array_values(array_diff($this->selectedTags, [$tagId]));
        $this->updateParentTags();
    }

    protected function updateParentTags()
    {
        // Only update the parent's tag selection state
        $this->dispatch('update-selected-tags', tags: $this->selectedTags);
    }

    public function createTag()
    {
        $this->validate();

        // First check if tag already exists
        $tag = Tag::firstOrCreate(
            ['name' => trim($this->newTagName)]
        );

        if (!in_array($tag->id, $this->selectedTags)) {
            $this->selectedTags[] = $tag->id;
            $this->updateParentTags();
        }

        $this->loadAvailableTags();
        $this->newTagName = ''; // Reset input field
        $this->searchQuery = ''; // Reset search query
    }

    public function deleteTag($tagId)
    {
        // First remove it from selected tags if it's there
        if (in_array($tagId, $this->selectedTags)) {
            $this->removeTag($tagId);
        }
        
        // First delete all transaction_tags relationships
        \App\Models\TransactionTags::where('tag_id', $tagId)->delete();
        
        // Delete the tag from database
        Tag::where('id', $tagId)->delete();
        
        // Reload available tags
        $this->loadAvailableTags();
    }

    public function render()
    {
        return view('livewire.components.tag-manager', [
            'filteredTags' => $this->getFilteredTagsProperty(),
            'selectedTagsObjects' => $this->getSelectedTagsObjectsProperty(),
        ]);
    }
}
