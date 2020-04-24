@extends('layouts.master')

@section('content')

@includeWhen(false, 'partials.announcement')

@include('partials.landing')
@include('partials.howitworks')
@include('partials.logos')
@include('partials.about')
@include('partials.faq')
@include('partials.stats')

@endsection
