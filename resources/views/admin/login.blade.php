<!DOCTYPE html>
<html lang="en">

<head>
    <title>
        Login | Meet Now
    </title>
    @include('admin.layout.common-head')
</head>
<style>
    .error{
        color: red;
    }
</style>
<body class="bg-gray-200">
    <main class="main-content  mt-0">
        <div class="page-header align-items-start min-vh-100" style="background-image: url('https://images.unsplash.com/photo-1497294815431-9365093b7331?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1950&q=80');">
            <span class="mask bg-gradient-dark opacity-6"></span>
            <div class="container my-auto">
                <div class="row">
                    <div class="col-lg-4 col-md-8 col-12 mx-auto">
                        <div class="card z-index-0 fadeIn3 fadeInBottom">
                            <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                                <div class="bg-gradient-primary shadow-primary border-radius-lg py-3 pe-1">
                                    <h4 class="text-white font-weight-bolder text-center mt-2 mb-0">Sign in</h4>
                                </div>
                            </div>
                            <div class="card-body">
                                <form class="text-start" name="login" id="login_form" method="post" action="{{ route('login-admin')}}">
                                    @csrf
                                    <div class="input-group input-group-outline my-3">
                                        <input type="email" name="email" id="email" class="form-control" placeholder="Email" value="{{ old('email') }}">
                                    </div>
                                    @if ($errors->has('email'))
                                        <span class="text-danger">{{ $errors->first('email') }}</span>
                                    @endif
                                    <p class="error"  id="email-error"></p>
                                    <div class="input-group input-group-outline mb-3">
                                        <input type="password" name="password" id="password" class="form-control" placeholder="Password">
                                    </div>
                                    @if ($errors->has('password'))
                                        <span class="text-danger">{{ $errors->first('password') }}</span>
                                    @endif
                                    <p class="error"  id="password-error"></p>
                                    <div class="form-check form-switch d-flex align-items-center mb-3">
                                        <input class="form-check-input" name="remember_me" type="checkbox" id="rememberMe">
                                        <label class="form-check-label mb-0 ms-3" for="rememberMe">Remember me</label>
                                    </div>
                                    @if ($errors->has('error'))
                                        <span class="text-danger">{{ $errors->first('error') }}</span>
                                    @endif
                                    <div class="text-center">
                                        <button type="submit" class="btn bg-gradient-primary w-100 my-4 mb-2">Login</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    @include('admin.layout.common-end')
    <script>
        $('#login_form').validate({
            rules: {
                email: {
                    required: true,
                    email: true
                },
                password: {
                    required: true,
                    minlength: 6
                }
            },
            messages: {
                email: {
                    required: "Please enter your email",
                    email: "Please enter a valid email address"
                },
                password: {
                    required: "Please enter your password",
                    minlength: "Your password must be at least 6 characters long"
                }
            },
            errorPlacement: function(error, element) {
                var elementId = element.attr('id'); 
                error.appendTo("#" + elementId + "-error");
            }
        });
    </script>
</body>

</html>