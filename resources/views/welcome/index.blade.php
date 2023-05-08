@extends('layouts.app')

@section('site_title', formatTitle([__('Welcome'), config('settings.title')]))


@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="bg-base-1 flex-fill">
    <div class="container py-3 my-3">
        <div class="row">
            <div class="col-12">
                @include('shared.breadcrumbs', ['breadcrumbs' => [
                    ['url' => request()->is('admin/*') ? route('admin.dashboard') : route('dashboard'), 'title' => request()->is('admin/*') ? __('Admin') : __('Home')],
                    ['title' => __('Welcome')],
                ]])
                <div class="d-flex align-items-end mb-3">
                    <h1 class="h2 mb-3 text-break">{{ __('Welcome to Livia') }}</h1>
                </div>
            </div>
            <div class="col-12">
                <div class="row no-gutters bg-base-0 rounded shadow-sm mb-3 overflow-hidden">
                    <div class="col-12 text-center text-break" style="margin-top:150px; margin-bottom:150px">
                        <h1 class="display-4 mb-0 font-weight-bold">
                            {{ __('Welcome to join AskLivia!') }}
                        </h1>

                        <div class="pt-2 d-flex flex-column flex-sm-row justify-content-center">
                            <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg font-size-lg align-items-center mt-3">{{ __('Let\'s start!') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    fbq('track', 'Lead');
</script>
@endsection

@include('shared.sidebars.user')