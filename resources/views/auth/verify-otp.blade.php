@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Verify OTP</h5>
                        <form method="POST" action="{{ route('verify-otp') }}">
                            @csrf

                            <div class="form-group">
                                <label for="otp">OTP</label>
                                <input id="otp" type="text" class="form-control @error('otp') is-invalid @enderror"
                                    name="otp" value="{{ old('otp') }}" required autofocus>

                                @error('otp')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">
                                    Verify OTP
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection