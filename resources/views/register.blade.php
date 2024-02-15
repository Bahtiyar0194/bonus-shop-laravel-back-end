@extends('layouts.main')

@section('title', config('app.name').' | '.__('auth.registration_title'))
@section('description', $sponsor->last_name.' '.$sponsor->first_name.' приглашает Вас в '.config('app.name'))

@section('content')
<div class="container-fluid">
	<div class="row justify-content-center align-items-center">
		<div class="col-12 col-sm-8 col-md-6 col-lg-4 py-4">
			<form method="post" action="{{ route('validate.register_form') }}" enctype="multipart/form-data" novalidate>
				@csrf
				<div class="row">
					<div class="col-12 mb-3">
						<h3 class="mb-0">{{__('auth.registration_title')}}</h3>
					</div>

					<div class="col-12 mb-4">
						<span>{{__('auth.your_sponsor')}}: <b>{{$sponsor->last_name}} {{$sponsor->first_name}}</b></span>
						<input type="hidden" name="sponsor_id" value="{{$sponsor->user_id}}">
					</div>

					<div class="col-12">
						<div class="form-group">
							@error('first_name')
							<label class="text-danger">
								<b>{{ $message }}</b>
							</label>
							@else
							<label>{{__('validation.attributes.first_name')}} *</label>
							@enderror
							<input type="text" value="{{ old('first_name') }}" class="form-control @error('first_name') is-invalid @elseif(old('first_name')) is-valid @enderror" name="first_name" id="first_name">
						</div>
					</div>

					<div class="col-12">
						<div class="form-group">
							@error('last_name')
							<label class="text-danger">
								<b>{{ $message }}</b>
							</label>
							@else
							<label>{{__('validation.attributes.last_name')}} *</label>
							@enderror
							<input type="text" value="{{ old('last_name') }}" class="form-control @error('last_name') is-invalid @elseif(old('last_name')) is-valid @enderror" name="last_name" id="last_name">
						</div>
					</div>

					<div class="col-12">
						<div class="form-group">
							@error('login')
							<label class="text-danger">
								<b>{{ $message }}</b>
							</label>
							@else
							<label>{{__('validation.attributes.login')}} *</label>
							@enderror
							<input type="text" value="{{ old('login') }}" class="form-control @error('login') is-invalid @elseif(old('login')) is-valid @enderror" name="login" id="login">
						</div>
					</div>

					<div class="col-12">
						<div class="form-group">
							@error('iin')
							<label class="text-danger">
								<b>{{ $message }}</b>
							</label>
							@else
							<label>{{__('validation.attributes.iin')}} *</label>
							@enderror
							<input type="number" value="{{ old('iin') }}" class="form-control @error('iin') is-invalid @elseif(old('iin')) is-valid @enderror" name="iin" id="iin">
						</div>
					</div>

					<div class="col-12">
						<div class="form-group">
							@error('email')
							<label class="text-danger">
								<b>{{ $message }}</b>
							</label>
							@else
							<label>{{__('validation.attributes.email')}} *</label>
							@enderror
							<input type="text" value="{{ old('email') }}" class="form-control @error('email') is-invalid @elseif(old('email')) is-valid @enderror" name="email" id="email">
						</div>
					</div>

					<div class="col-12">
						<div class="form-group">
							@error('phone')
							<label class="text-danger">
								<b>{{ $message }}</b>
							</label>
							@else
							<label>{{__('validation.attributes.phone')}} *</label>
							@enderror
							<input value="{{ old('phone') }}" class="form-control @error('phone') is-invalid @elseif(old('phone')) is-valid @enderror" name="phone" id="phone">
						</div>
					</div>

					<div class="col-12">
						<div class="form-group">
							@error('city')
							<label class="text-danger">
								<b>{{ $message }}</b>
							</label>
							@else
							<label>{{__('validation.attributes.city')}} *</label>
							@enderror

							<select class="form-control @error('city') is-invalid @elseif(old('city')) is-valid @enderror" name="city" id="city">
								<option disabled selected value="">{{__('validation.custom.city.numeric')}}</option>
								@foreach ($cities as $region)
								<optgroup label="{{$region['title']}}">
									@foreach ($region['data'] as $city)
									<option value="{{$city['id']}}" <?php if(old('city') == $city['id']){echo 'selected';}?>>{{$city['name']}}</option>
									@endforeach
								</optgroup>
								@endforeach
							</select>
						</div>
					</div>

					<div class="col-12">
						<div class="form-group">
							@error('password')
							<label class="text-danger">
								<b>{{ $message }}</b>
							</label>
							@else
							<label>{{__('validation.attributes.password')}} *</label>
							@enderror
							<div class="input-group">
								<input value="{{ old('password') }}" type="password" class="form-control @error('password') is-invalid @elseif(old('password')) is-valid @enderror" name="password" id="password" aria-describedby="button-addon2">
								<div class="input-group-append">
									<button onclick="showPassword(this)" class="btn btn-outline-secondary" type="button" id="button-addon2"><i class="eye far fa-eye-slash"></i></button>
								</div>
							</div>
						</div>
					</div>

					<div class="col-12">
						<div class="form-group">
							@error('password_confirmation')
							<label class="text-danger">
								<b>{{ $message }}</b>
							</label>
							@else
							<label>{{__('validation.attributes.password_confirmation')}} *</label>
							@enderror
							<input value="{{ old('password_confirmation') }}" type="password" class="form-control @error('password_confirmation') is-invalid @elseif(old('password_confirmation')) is-valid @enderror" name="password_confirmation" id="password_confirmation">
						</div>
					</div>

					<div class="col-12">
						<button type="submit" class="btn btn-primary w-100">{{__('auth.registration_title')}}</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
@endsection