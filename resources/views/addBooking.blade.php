@extends('layouts.app')
@section('content')
<div>
    
    <div class="container-fluid p-5">
        <div class="booking col-4 mx-auto p-4  border border-success border-2">
            <h4 class="text-uppercase text-center">Add Booking</h4>
            <form method="POST" action="{{route('booking')}}" enctype="multipart/form-data">
                @csrf
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label for="Fullname">Full Name</label>
                            <input class="form-control @error('Fullname') is-invalid @enderror" required id="Fullname" name="Fullname" type="text" placeholder="Enter your Full Name" value="{{ old('Fullname') }}">
                            @error('Fullname')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label for="Email">Email</label>
                            <input class="form-control @error('Email') is-invalid @enderror" required id="Email" name="Email" type="text" placeholder="Enter you Email" value="{{ old('Email') }}">
                            @error('Email')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <div class="form-group">
                        <label for="check-in">Check-in:</label>
                        <input type="date" id="check-in" class="form-control">
                            @error('date')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label for="Travelers">Travelers</label>
                            <input class="form-control @error('Travelers') is-invalid @enderror" required id="Travelers" name="Travelers" type="double" placeholder="Enter Travelers " value="{{ old('Travelers') }}">
                            @error('Travelers')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>
                </div>
                <!-- /.row-->
               
                <div class=" mt-5">
                    <button class="btn-dark-green ">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@section('javascript')
<script src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script type="text/javascript">
    $(document).ready(function(e) {
        $('#img').change(function() {
            let reader = new FileReader();
            reader.onload = (e) => {
                $('#preview-image-before-upload').attr('src', e.target.result);
            }
            reader.readAsDataURL(this.files[0]);
        });


    });
</script>


@endsection
