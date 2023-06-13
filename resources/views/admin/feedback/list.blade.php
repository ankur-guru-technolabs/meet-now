@extends('admin.layout.app')
@section('title', 'Feedback List')
@section('content')

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                        <h6 class="text-white text-capitalize ps-3">Feedback table</h6>
                    </div>
                </div>
                <div class="card-body px-0 pb-2">
                    <div class="table-responsive p-0 mx-3">
                        <table id="feedback_list_table" class="display" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($feedbacks as $feedback)
                                <tr>
                                    <td>{{$feedback->id}}</td>
                                    <td>{{$feedback->name}}</td>
                                    <td>{{$feedback->email}}</td>
                                    <td>
                                        <a href="" data-bs-toggle="modal" data-bs-target="#feedbackModal" data-id="{{$feedback->id}}" data-name="{{$feedback->name}}" data-email="{{$feedback->email}}" data-description="{{$feedback->description}}">
                                            <i class="material-icons opacity-10">visibility</i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="feedbackModal" tabindex="-1" role="dialog" aria-labelledby="feedbackModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title font-weight-normal" id="feedbackModalLabel">Feedback</h5>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-3"><b>Name:</b></div>
                                <div class="col-9" id="name_value"></div>
                            </div>
                            <div class="row">
                                <div class="col-3"><b>Email:</b></div>
                                <div class="col-9" id="email_value"></div>
                            </div>
                            <div class="row">
                                <div class="col-3"><b>Description:</b></div>
                                <div class="col-9" id="description_value"></div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn bg-gradient-primary" data-bs-dismiss="modal">Close</button>
                        </div>
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
        $('#feedback_list_table').DataTable();
    });
    $('#feedbackModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        var name = button.data('name');
        var email = button.data('email');
        var description = button.data('description');
        $('#name_value').text(name);
        $('#email_value').text(email);
        $('#description_value').text(description);
    });
</script>
@endsection