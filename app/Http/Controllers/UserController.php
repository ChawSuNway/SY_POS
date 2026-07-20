<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('role')->orderBy('name')->paginate(20);

        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'role'     => ['required', Rule::in(['cashier', 'manager', 'admin'])],
            'password' => ['required', Password::min(6)],
            'is_active'=> ['boolean'],
        ]);

        User::create([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'role'      => $data['role'],
            'password'  => $data['password'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('users.index')->with('success', __('app.saved'));
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'role'     => ['required', Rule::in(['cashier', 'manager', 'admin'])],
            'password' => ['nullable', Password::min(6)],
            'is_active'=> ['boolean'],
        ]);

        // မိမိကိုယ်တိုင် admin ရာထူး/အသုံးပြုခွင့် မဖျက်မိစေရန်
        if ($user->id === $request->user()->id) {
            $data['role'] = 'admin';
            $data['is_active'] = true;
        }

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->role = $data['role'];
        $user->is_active = $request->boolean('is_active', true);
        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }
        $user->save();

        return redirect()->route('users.index')->with('success', __('app.saved'));
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'မိမိကိုယ်တိုင်ကို ဖျက်၍မရပါ။');
        }

        $user->delete();

        return back()->with('success', __('app.deleted'));
    }
}
