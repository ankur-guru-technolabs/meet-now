@extends('admin.layout.app')
@section('title', 'User List')
@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                        <h6 class="text-white text-capitalize ps-3">Users table</h6>
                    </div>
                </div>
                <div class="card-body px-0 pb-2">
                    <div class="table-responsive p-0 mx-3">
                        <table id="users_list_table" class="display" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone no</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $user)
                                <tr>
                                    <td>{{$user->id}}</td>
                                    <td>{{$user->name}}</td>
                                    <td>{{$user->email}}</td>
                                    <td>{{$user->phone_no}}</td>
                                    <td>
                                        <div class="form-switch">
                                            <input class="form-check-input bg-gradient-primary user_status_switch" id="user_status_switch" type="checkbox" role="switch" data-id="{{$user->id}}" @if($user->status == 1) checked @endif>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script>
    $(document).ready(function() {
        $('#users_list_table').DataTable();
    });
    $('.user_status_switch').change(function() {
        var status = $(this).prop('checked') ? 1 : 0;
        var id = $(this).data('id');
        var url = "{{ route('users.status-update') }}";
        $.ajax({
            url: url,
            type: "POST",
            data: { id: id,status: status, "_token": "{{ csrf_token() }}" },
            success: function(data) {
                window.location.reload();
            },
            error: function(xhr) {
                console.log(xhr.responseJSON.message);
            }
        });
    });
</script>
@endsection

