@extends('layouts.app')

@section('content')
<h1 class="text-2xl font-bold">Welcome, {{ auth()->user()->name }}!</h1>
@endsection