@extends('admin.layout.app')
@section('title', 'Notification')
@section('content')

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                        <h6 class="text-white text-capitalize ps-3">Notification</h6>
                    </div>
                </div>
                <div class="card-body px-0 pb-2">
                    <div class="p-4">
                        <form method="post" action="{{route('notification.send')}}" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="input-group input-group-dynamic mb-3 focused is-focused">
                                    <label class="form-label">Title</label>
                                    <input type="text" class="form-control" name="title" autocomplete="off">
                                </div>
                                @if($errors->has('title'))
                                    <small class="text-danger mb-3" >
                                        {{ $errors->first('title') }}
                                    </small>
                                @endif
                            </div>
                            <div class="row">
                                <div class="input-group input-group-dynamic mb-3 focused is-focused">
                                    <label class="form-label">Message</label>
                                    <input type="text" class="form-control" name="message" autocomplete="off">
                                </div>
                                @if($errors->has('message'))
                                    <small class="text-danger mb-2" >
                                        {{ $errors->first('message') }}
                                    </small>
                                @endif
                            </div>
                            <div class="row pt-4">
                                <div class="col s12 m12 input-field">
                                    <button type="submit" class="btn bg-gradient-primary">Send</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')

@endsection