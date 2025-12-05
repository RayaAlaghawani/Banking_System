<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class userRepository
{
    public function getByEmail(string $email):?User
    {
        return User::where('email', $email)->first();
    }
    public function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'government_agencie_id' => $data['government_agencie_id'],
            'phone' => $data['phone'],
            'national_id' => $data['national_id'],
            'email_verified_at' => now(),
        ]);
    }



    public function getByName($name)
    {
        return Role::where('name', $name)->first();
    }

    public function getById($id)
    {
        return User::findOrFail($id);

    }

}
