<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;

class UserManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $status = ''; // empty means all, '1' active, '0' inactive

    /** @var list<string> */
    public array $assignableRoles = [User::ROLE_ADMIN, User::ROLE_MEMBER];

    public function assignRole(int $userId, string $role): void
    {
        if (! in_array($role, $this->assignableRoles, true)) {
            return;
        }

        $user = User::findOrFail($userId);
        $user->update(['role' => $role]);
        session()->flash('success', "Role {$role} berhasil diberikan ke {$user->name}.");
    }

    public function toggleActive($userId)
    {
        $user = User::findOrFail($userId);
        $user->update(['is_active' => !$user->is_active]);
        session()->flash('success', 'Status pengguna diperbarui.');
    }

    public function deleteUser($userId)
    {
        $user = User::findOrFail($userId);
        
        // Safety check to prevent deleting self
        if ($user->id === auth()->id()) {
            session()->flash('error', 'Anda tidak dapat menghapus diri sendiri.');
            return;
        }

        // Delete listings via Eloquent to ensure media cleanup
        foreach ($user->listings as $listing) {
            $listing->delete();
        }

        $user->delete();
        session()->flash('success', 'User dan semua data terkait berhasil dihapus.');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatus()
    {
        $this->resetPage();
    }

    public function render()
    {
        $users = User::query()
            ->when($this->search, function($q) {
                $q->where(function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->status !== '', fn($q) => $q->where('is_active', $this->status))
            ->latest()
            ->paginate(15);

        return view('livewire.admin.user-manager', [
            'users' => $users,
            'roles' => $this->assignableRoles,
        ])->layout('layouts.main');
    }
}
