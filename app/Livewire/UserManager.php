<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

use Livewire\Attributes\Title;

#[Title('Manajemen Pengguna')]
class UserManager extends Component
{
    use WithPagination;

    public $name;
    public $email;
    public $password;
    public $userId;
    public $isUpdateMode = false;
    public $roles = [];
    public $selectedRoles = [];
    public $showModal = false;
    public $search = ''; // Added for search functionality // New property for modal visibility

    public function mount()
    {
        $this->roles = \Spatie\Permission\Models\Role::pluck('name', 'name')->toArray();
    }

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->userId),
            ],
            'password' => $this->isUpdateMode ? 'nullable|string|min:8' : 'required|string|min:8',
            'selectedRoles' => 'required|array|min:1', // Ensure at least one role is selected
            'selectedRoles.*' => 'exists:roles,name', // Ensure selected roles exist
        ];
    }

    protected $messages = [
        'name.required' => 'Nama wajib diisi.',
        'email.required' => 'Email wajib diisi.',
        'email.email' => 'Email tidak valid.',
        'email.unique' => 'Email sudah terdaftar.',
        'password.required' => 'Password wajib diisi.',
        'password.min' => 'Password minimal 8 karakter.',
        'selectedRoles.required' => 'Setidaknya satu peran harus dipilih.',
        'selectedRoles.min' => 'Setidaknya satu peran harus dipilih.',
    ];

    public function render()
    {
        $users = User::query()
                            ->when($this->search, function ($query) {
                                $query->where('name', 'like', '%' . $this->search . '%')
                                      ->orWhere('email', 'like', '%' . $this->search . '%');
                            })
                            ->latest()
                            ->paginate(5);
        return view('livewire.user-manager', compact('users'));
    }

    public function save()
    {
        $this->validate();

        if ($this->isUpdateMode) {
            $user = User::find($this->userId);
            $data = [
                'name' => $this->name,
                'email' => $this->email,
            ];
            if (!empty($this->password)) {
                $data['password'] = Hash::make($this->password);
            }
            $user->update($data);
            $user->syncRoles($this->selectedRoles);
            session()->flash('message', 'Pengguna berhasil diperbarui.');
        } else {
            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
            ]);
            $user->assignRole($this->selectedRoles);
            session()->flash('message', 'Pengguna berhasil ditambahkan.');
        }

        $this->closeModal(); // Close modal and reset form
    }

    public function createUser() // Method to open modal for new user
    {
        $this->resetInput(); // Clear form fields
        $this->showModal = true; // Open the modal
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->isUpdateMode = true;
        $this->selectedRoles = $user->getRoleNames()->toArray();
        $this->showModal = true; // Open modal for edit
    }

    public function delete($id)
    {
        User::find($id)->delete();
        session()->flash('message', 'Pengguna berhasil dihapus.');
    }

    public function resetInput()
    {
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->userId = null;
        $this->isUpdateMode = false;
        $this->selectedRoles = [];
        $this->resetErrorBag(); // Clear validation errors
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetInput(); // Reset form when closing modal
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }
}