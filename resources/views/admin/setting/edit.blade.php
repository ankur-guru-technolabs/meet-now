@extends('admin.layout.app')
@section('title', 'Static Page Edit')
@section('content')

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                        <h6 class="text-white text-capitalize ps-3">Static Page Edit</h6>
                    </div>
                </div>
                <div class="card-body px-0 pb-2">
                    <div class="p-4">
                        <form method="post" action="{{route('static-pages.page-update')}}" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" class="form-control" value="{{$settings->id ?? ''}}" name="id">
                            <input type="hidden" class="form-control" value="{{$settings->key ?? ''}}" name="key">
                            <div class="row">
                                <div class="input-group input-group-dynamic mb-4 focused is-focused">
                                    <label class="form-label">Title</label>
                                    <input type="text" class="form-control" value="{{$settings->title ?? ''}}" name="title" autocomplete="off">
                                </div>
                                @if($errors->has('title'))
                                    <small class="text-danger mb-2" >
                                        {{ $errors->first('title') }}
                                    </small>
                                @endif
                            </div>
                            @if($settings->key == 'share_ios')
                                <div class="row">
                                    <div class="input-group input-group-dynamic focused is-focused">
                                        <label class="form-label">IOS link</label>
                                        <input type="text" class="form-control" value="{{ $settings->value ?? ''}}" name="description" autocomplete="off">
                                    </div>
                                    @if($errors->has('description'))
                                    <small class="text-danger">
                                        {{ $errors->first('description') }}
                                    </small>
                                    @endif
                                </div>
                            @elseif($settings->key == 'share_android')
                                <div class="row">
                                    <div class="input-group input-group-dynamic focused is-focused">
                                        <label class="form-label">Android link</label>
                                        <input type="text" class="form-control" value="{{ $settings->value ?? ''}}" name="description" autocomplete="off">
                                    </div>
                                    @if($errors->has('description'))
                                        <small class="text-danger">
                                            {{ $errors->first('description') }}
                                        </small>
                                    @endif
                                </div>
                            @else
                                <div class="row">
                                    <div class="col s12 m12 input-field focused is-focused">
                                        <label for="description" class="lable-ck">Description</label>
                                        <textarea id="description" name="description" class="ckeditor form-control validate" name="description">{{ $settings->value ?? ''}}</textarea>
                                    </div>
                                    @if($errors->has('description'))
                                        <small class="text-danger mt-2">
                                            {{ $errors->first('description') }}
                                        </small>
                                    @endif
                                </div>
                            @endif
                            <div class="row pt-4">
                                <div class="col s12 m12 input-field">
                                    <button type="submit" class="btn bg-gradient-primary">Save</button>
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
<script src="https://cdn.ckeditor.com/4.21.0/standard/ckeditor.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $('.ckeditor').ckeditor();
    });
</script>
@endsection