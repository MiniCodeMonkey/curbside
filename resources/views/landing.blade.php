@extends('layouts.master')

@section('content')

<div class="relative bg-orange-600">
  <div class="max-w-screen-xl mx-auto py-3 px-3 sm:px-6 lg:px-8">
    <div class="pr-16 sm:text-center sm:px-16">
      <p class="font-medium text-white">
        <strong>Sorry!</strong> Kroger and Kroger-owned stores are not working right now. We are working on finding a solution.
      </p>
    </div>
  </div>
</div>

<landing :chains='@json($chains->map->name)'></landing>

@include('partials.howitworks')
@include('partials.logos')
@include('partials.about')
@include('partials.faq')
@include('partials.stats')

@endsection
