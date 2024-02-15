@extends('layouts.main')

@section('title', config('app.name').' | Скачивайте наше приложение в Google Play и App Store')
@section('description', '')

@section('content')
<div class="container-fluid">
	<div class="row justify-content-center align-items-center">
		<div class="col-12 col-sm-8 col-md-6 col-lg-4 pt-4">
			<h4>
				{{__('auth.congratulations', ['name' => @Auth::user()->first_name])}}
				<br>
				{{__('auth.registration_successful')}}
			</h4>

			<p>{{__('auth.get_bonuses')}}.</p>
			<p>{{__('auth.download_our_app')}}.</p>

			<div class="d-flex flex-wrap">
				<a class="mr-3" href="/">
					<img src="{{url('/images/app-store.svg')}}" style="height: 40px;" alt="">
				</a>

				<a href="/">
					<img src="{{url('/images/google-play.png')}}" style="height: 40px;" alt="">
				</a>
			</div>

			<img class="w-100 mt-4" src="{{url('/images/iphone.png')}}">
		</div>
	</div>
</div>
@endsection