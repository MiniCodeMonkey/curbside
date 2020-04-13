@extends('layouts.master')

@section('content')

@includeWhen(false, 'partials.announcement')

<landing :chains='@json($chains->map->name)'></landing>

@include('partials.howitworks')
@include('partials.logos')
@include('partials.about')
@include('partials.faq')
@include('partials.stats')

@endsection
