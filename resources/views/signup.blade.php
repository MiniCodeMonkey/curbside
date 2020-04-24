@extends('layouts.master')

@section('content')

<signup :chains='@json($chains->map->name)'></signup>

@endsection
