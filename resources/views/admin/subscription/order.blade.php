@extends('admin.layout.app')
@section('title', 'Order List')
@section('content')

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                        <h6 class="text-white text-capitalize ps-3">Order Table</h6>
                    </div>
                </div>
                <div class="card-body px-0 pb-2">
                    <div class="table-responsive p-0 mx-3">
                        <table id="subscription_order_list_table" class="display" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>Customer Name</th>
                                    <th>Purchase Date</th>
                                    <th>Expiry Date</th>
                                    <th>Plan Name</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orders as $key=>$order)
                                    <tr>
                                        <td>{{++$key}}</td>
                                        <td>{{$order->user->name}}</td>
                                        <td>{{date ('d -m - Y', strtotime($order->created_at))}}</td>
                                        <td>{{date ('d -m - Y', strtotime($order->expire_date))}}</td>
                                        <td>{{$orders[0]->subscriptionOrder['title']}}</td>
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
        $('#subscription_order_list_table').DataTable();
    });
</script>
@endsection