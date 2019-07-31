@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Dashboard</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                        @if(auth()->user()->isBanned)
                            <div class=" alert alert-danger">
                                You was  banned in a chat by an admin
                            </div>
                        @endif

                    You are logged in!
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
