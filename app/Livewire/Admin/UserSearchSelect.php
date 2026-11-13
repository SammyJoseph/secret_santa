<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Livewire\Attributes\On;
use Livewire\Component;

class UserSearchSelect extends Component
{
    public string $search = '';
    public ?int $selectedUserId = null;
    public ?string $selectedUserName = null;
    public ?string $selectedUserDni = null;

    public function mount(?int $initialUserId = null)
    {
        if ($initialUserId) {
            $this->selectUser($initialUserId, false);
        }
    }

    public function selectUser(int $id, bool $dispatch = true)
    {
        $user = User::with('familyGroup:id,name,year')->find($id);

        if ($user) {
            $this->selectedUserId = $user->id;
            $this->selectedUserName = $user->name;
            $this->selectedUserDni = $user->dni;
            $this->search = '';

            if ($dispatch) {
                $this->dispatch('user-selected', user: [
                    'id' => $user->id,
                    'name' => $user->name,
                    'dni' => $user->dni,
                    'nickname' => $user->nickname ?? '',
                    'email' => $user->email ?? '',
                    'profile_photo_url' => $user->profile_photo_url,
                ]);
            }
        }
    }

    public function clearSelection()
    {
        $this->selectedUserId = null;
        $this->selectedUserName = null;
        $this->selectedUserDni = null;
        $this->search = '';

        $this->dispatch('user-cleared');
    }

    #[On('reset-user-search')]
    public function handleExternalReset()
    {
        $this->selectedUserId = null;
        $this->selectedUserName = null;
        $this->selectedUserDni = null;
        $this->search = '';
    }

    #[On('select-user-by-dni')]
    public function handleSelectByDni(string $dni)
    {
        $user = User::where('dni', $dni)->first();
        if ($user) {
            $this->selectedUserId = $user->id;
            $this->selectedUserName = $user->name;
            $this->selectedUserDni = $user->dni;
            $this->search = '';
        }
    }

    public function render()
    {
        $term = trim($this->search);

        $users = User::query()
            ->when(filled($term), function ($query) use ($term) {
                $query->where(function ($q) use ($term) {
                    $q->where('name', 'like', "%{$term}%")
                      ->orWhere('dni', 'like', "%{$term}%")
                      ->orWhere('nickname', 'like', "%{$term}%");
                });
            })
            ->with('familyGroup:id,name,year')
            ->orderBy('name', 'asc')
            ->limit(40)
            ->get();

        return view('livewire.admin.user-search-select', [
            'users' => $users,
        ]);
    }
}
