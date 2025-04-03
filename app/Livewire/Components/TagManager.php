<?php

namespace App\Livewire\Components;

use App\Models\Tag;
use Illuminate\Support\Collection;
use Livewire\Component;

class TagManager extends Component
{
    public $selectedTags = [];

    public $availableTags;

    public $searchQuery = '';

    public $isEditing = false;

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
        $this->dispatch('update-selected-tags', tags: $this->selectedTags);
    }

    public function createTag()
    {
        $this->validate([
            'searchQuery' => 'required|string|min:2|max:50',
        ]);

        $tagName = trim($this->searchQuery);

        $tag = Tag::firstOrCreate(['name' => $tagName]);

        if (! in_array($tag->id, $this->selectedTags)) {
            $this->selectedTags[] = $tag->id;
            $this->updateParentTags();
        }

        $this->searchQuery = '';
        $this->loadAvailableTags();
    }

    public function deleteTag($tagId)
    {
        if (in_array($tagId, $this->selectedTags)) {
            $this->removeTag($tagId);
        }

        \App\Models\TransactionTags::where('tag_id', $tagId)->delete();
        Tag::where('id', $tagId)->delete();
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
