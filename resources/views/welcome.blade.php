@extends('layout.main')

@section('content')
@include('layout.header')
<div class="d-flex justify-content-center align-items-start">
    <div class="row w-100 justify-content-center mt-3">
        <div class="col-sm-12 col-md-6 col-lg-6 mb-2">
            <div class="mx-1 flex-wrap d-flex justify-content-center">
                <a href="{{ route('family.index') }}" class="btn btn-light m-2 shadow fs-1 fw-bolder" style="width: 140px; height: 100px">Family Material</a>
                <a href="{{ route('material.index') }}" class="btn btn-light m-2 shadow fs-1 fw-bolder" style="width: 140px; height: 100px">Material</a>
                <a href="{{ route('task.index') }}" class="btn btn-light m-2 shadow fs-1 fw-bolder" style="width: 140px; height: 100px">Task</a>
            </div>
        </div>
    </div>
</div>
@endsection