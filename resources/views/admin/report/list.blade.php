@extends('admin.layout.app')
@section('title', 'Report List')
@section('content')

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                        <h6 class="text-white text-capitalize ps-3">Report Table</h6>
                    </div>
                </div>
                <div class="card-body px-0 pb-2">
                    <div class="table-responsive p-0 mx-3">
                        <table id="report_list_table" class="display" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>Reporter</th>
                                    <th>ReportedUser</th>
                                    <th>Reason</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($report as $key=>$report)
                                    <tr>
                                        <td>{{ ++$key }}</td>
                                        <td>{{$report->reporter->name}}</td>
                                        <td>{{$report->reportedUser->name}}</td>
                                        <td>{{strlen($report->message) > 100 ? substr($report->message,0,100)."..." : $report->message;}}</td>
                                        <td>
                                            <div class="form-switch">
                                                <input class="form-check-input bg-gradient-primary user_status_switch" id="user_status_switch" type="checkbox" role="switch" data-id="{{$report->reportedUser->id}}" @if($report->reportedUser->status == 1) checked @endif>
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
        $('#report_list_table').DataTable();
    });
    $('.user_status_switch').change(function() {
        var status = $(this).prop('checked') ? 1 : 0;
        var id = $(this).data('id');
        var url = "{{ route('report.user-block') }}";
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