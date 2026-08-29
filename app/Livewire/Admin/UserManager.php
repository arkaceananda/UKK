<?php

namespace App\Livewire\Admin;

use App\Enums\UserRole;
use App\Models\User;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

class UserManager extends Component
{
    use WithPagination;

    public bool $showUserModal = false;

    public ?int $editingUserId = null;

    #[Validate('required|string|min:2|max:100')]
    public string $name = '';

    #[Validate('required|string|email|max:255')]
    public string $email = '';

    #[Validate('required|string|min:8|max:100')]
    public string $password = '';

    #[Validate('required|string|min:8|max:100|same:password')]
    public string $password_confirmation = '';

    #[Validate('required|in:Admin,Kasir')]
    public string $role = UserRole::Kasir->value;

    public string $search = '';

    protected $queryString = ['search' => ['except' => '']];

    public function openCreateUserModal(): void
    {
        $this->resetValidation();
        $this->reset(['editingUserId', 'name', 'email', 'password', 'password_confirmation']);
        $this->role = UserRole::Kasir->value;
        $this->showUserModal = true;
    }

    public function openEditUserModal(int $userId): void
    {
        $this->resetValidation();
        $user = User::findOrFail($userId);
        $this->editingUserId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->password = '';
        $this->password_confirmation = '';
        $this->role = $user->role->value;
        $this->showUserModal = true;
    }

    public function saveUser(): void
    {
        $rules = [
            'name' => 'required|string|min:2|max:100',
            'email' => 'required|string|email|max:255|unique:users,email',
            'role' => 'required|in:Admin,Kasir',
        ];

        if ($this->editingUserId) {
            $rules['email'] = 'required|string|email|max:255|unique:users,email,'.$this->editingUserId;
            $rules['password'] = 'nullable|string|min:8|max:100|confirmed';
        } else {
            $rules['password'] = 'required|string|min:8|max:100|confirmed';
        }

        $this->validate($rules);

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
        ];

        if ($this->password !== '') {
            $data['password'] = $this->password;
        }

        if ($this->editingUserId) {
            User::findOrFail($this->editingUserId)->update($data);
            $this->dispatch('notify', message: 'User berhasil diperbarui!', type: 'success');
        } else {
            User::create($data);
            $this->dispatch('notify', message: 'User baru berhasil ditambahkan!', type: 'success');
        }

        $this->showUserModal = false;
        $this->reset(['editingUserId', 'name', 'email', 'password', 'password_confirmation']);
    }

    public function deleteUser(int $userId): void
    {
        $user = User::findOrFail($userId);

        if ($user->id === auth()->id()) {
            $this->dispatch('notify', message: 'Tidak bisa menghapus akun sendiri', type: 'error');

            return;
        }

        $user->delete();
        $this->dispatch('notify', message: 'User berhasil dihapus.', type: 'info');
    }

    public function render()
    {
        $users = User::query()
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%")->orWhere('email', 'like', "%{$this->search}%"))
            ->orderBy('role')
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.admin.user-manager', [
            'users' => $users,
        ]);
    }
}
