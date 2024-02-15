@extends('layouts.main')

@section('title', config('app.name'))
@section('description', 'Воспользуйтесь акцией и получите гарантированный приз')
@section('image', url('/api/v1/stocks/get_image/').'/'.$stock_id)

@section('content')
<div style="position: relative;">
	<div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-repeat: no-repeat; background-position: center; background-size: cover; filter: blur(15px) brightness(0.9); background-image: url('{{url('/api/v1/stocks/get_image/').'/'.$stock_id}}');">
</div>
<div class="container-fluid py-5">
	<div class="row justify-content-center align-items-center">
		<div class="col-12 col-sm-8 col-md-6 col-lg-4">
			<img class="w-100 mb-4" style="border-radius: 10px" src="{{url('/api/v1/stocks/get_image/').'/'.$stock_id}}">
			<a class="btn btn-block btn-outline-light" href="{{url('/auth/register/').'/'.$login}}">{{__('auth.registration_title')}}</a>
		</div>
	</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous"></script>
@endsection