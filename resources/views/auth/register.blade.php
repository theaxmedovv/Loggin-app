@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto mt-10">
    <form method="POST" action="{{ route('register') }}">
        @csrf
        <div>
            <label>Name</label>
            <input type="text" name="name" required class="border p-2 w-full">
        </div>
        <div class="mt-4">
            <label>Email</label>
            <input type="email" name="email" required class="border p-2 w-full">
        </div>
        <div class="mt-4">
            <label>Password</label>
            <input type="password" name="password" required class="border p-2 w-full">
        </div>
        <div class="mt-4">
            <button type="submit" class="bg-blue-500 text-white p-2 rounded">Register</button>
        </div>
    </form>
</div>
@endsection