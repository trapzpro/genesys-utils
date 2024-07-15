<?php

namespace App\Livewire;

use Livewire\Component;

class OrgSelector extends Component
{
    public $sourceOrg;
    public $destinationOrg;
    public $count = 1;

    public $items = [
        'Users' => ['Users' => 'User Details', 'UsersRoles' => 'Roles'],
        'Groups' => ['Groups' => 'Group Details', 'GroupPerms' => 'Group Permissions'],

        // Add more items and subitems as needed
    ];
    public function increment()
    {
        $this->count++;
    }

    public function decrement()
    {
        $this->count--;
    }

    public $selectedItems = [];

    public function toggleItem($item)
    {
        if (in_array($item, $this->selectedItems)) {
            $this->selectedItems = array_diff($this->selectedItems, [$item]);
        } else {
            $this->selectedItems[] = $item;
        }
    }

    public $organizations = [
        'prus' => 'Prod',
        'org2' => 'DR',
        'org3' => 'QA',
        // Add more organizations as needed
    ];



    public function syncOrgs()
    {
        // Add your sync logic here
        // For example:
        // $this->syncOrganizations($this->sourceOrg, $this->destinationOrg);

        session()->flash('message', 'Organizations have been synced successfully.');
    }

    public function render()
    {
        return view('livewire.org-selector');
    }
}
