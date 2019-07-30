@extends('layouts.app')

@section('content')
    <div class="container">
        <chat :user="{{ auth()->user() }}">
         >
        </chat>
        @if(auth()->user()->isAdmin())
            <user-list :users="{{ $users }}"></user-list>
         @endif
    </div>
 @endsection
