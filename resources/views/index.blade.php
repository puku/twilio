@extends('layouts.master')

@section('title', 'Test task')


@section('content')
    @foreach ($countries as $country)

        <div class="row">
            <div class="col-md-4">

                <a href="{{ route('phone-number', ['code' => $country->short_code]) }}">
                    <img src="img/{{  $country->flag_src }}" style="max-width: 200px; max-height: 200px">
                </a>
            </div>
        </div>
    @endforeach
@endsection