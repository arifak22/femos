<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>{{env('APP_NAME') . ' | Sign In'}}</title>
    <style>
      #loader {
        transition: all 0.3s ease-in-out;
        opacity: 1;
        visibility: visible;
        position: fixed;
        height: 100vh;
        width: 100%;
        background: #fff;
        z-index: 90000;
      }

      #loader.fadeOut {
        opacity: 0;
        visibility: hidden;
      }

      .spinner {
        width: 40px;
        height: 40px;
        position: absolute;
        top: calc(50% - 20px);
        left: calc(50% - 20px);
        background-color: #333;
        border-radius: 100%;
        -webkit-animation: sk-scaleout 1.0s infinite ease-in-out;
        animation: sk-scaleout 1.0s infinite ease-in-out;
      }

      @-webkit-keyframes sk-scaleout {
        0% { -webkit-transform: scale(0) }
        100% {
          -webkit-transform: scale(1.0);
          opacity: 0;
        }
      }

      @keyframes sk-scaleout {
        0% {
          -webkit-transform: scale(0);
          transform: scale(0);
        } 100% {
          -webkit-transform: scale(1.0);
          transform: scale(1.0);
          opacity: 0;
        }
      }
    </style>
    <link rel="stylesheet" href="{{url('style.css')}}">
    <link rel="stylesheet" href="{{url('assets')}}/_custom/css/style.css"> 
    <script type="text/javascript" src="{{url('vendor.js')}}"></script>
    <script type="text/javascript" src="{{url('bundle.js')}}"></script>
  </head>
  <body class="app">
    <div id='loader'>
      <div class="spinner"></div>
    </div>

    <script>
      window.addEventListener('load', function load() {
        const loader = document.getElementById('loader');
        setTimeout(function() {
          loader.classList.add('fadeOut');
        }, 300);
      });
    </script>
    <div class="peers ai-s fxw-nw h-100vh">
      <div class="d-n@sm- peer peer-greed h-100 pos-r bgr-n bgpX-c bgpY-c bgsz-cv" style='background-image: url("{{url('assets/static/images/bg.jpg')}}")'>
        <div class="pos-a centerXY">
          <div class="bgc-white bdrs-50p pos-r" style='width: 120px; height: 120px;'>
            <img class="pos-a centerXY" src="{{url('assets/static/images/logo.png')}}" alt="">
          </div>
        </div>
      </div>
      <div class="col-12 col-md-4 peer pX-40 pY-80 h-100 bgc-white scrollable pos-r login-page" style='min-width: 320px;'>
        <h4 class="fw-300 c-grey-900 mB-40">Login</h4>
        <form>
          <div class="form-group">
            <label class="text-normal text-dark">Username</label>
            <input type="text" name="username" class="form-control" placeholder="Username">
          </div>
          <div class="form-group">
            <label class="text-normal text-dark">Password</label>
            <input type="password" name="password" class="form-control" placeholder="Password">
          </div>
          <div class="form-group">
            <div class="peers ai-c jc-sb fxw-nw">
              <div class="peer">
                <div class="checkbox checkbox-circle checkbox-info peers ai-c">
                    <a class="peer peer-greed go-reset" href="">Lupa Password ?</a>
                </div>
              </div>
              <div class="peer">
                <button id="signin" class="btn btn-primary">Login</button>
              </div>
            </div>
          </div>
        </form>
      </div>
      
      <div class="col-12 col-md-4 peer pX-40 pY-80 h-100 bgc-white scrollable pos-r reset-page" style='min-width: 320px;'>
        <h4 class="fw-300 c-grey-900 mB-40">Lupa Password</h4>
        <form>
          <div class="form-group">
            <label class="text-normal text-dark">Username</label>
            <input type="text" name="username" class="form-control" placeholder="Username">
          </div>
          <div class="form-group">
            <div class="peers ai-c jc-sb fxw-nw">
              <div class="peer">
                <div class="checkbox checkbox-circle checkbox-info peers ai-c">
                    <a class="peer peer-greed go-login" href=""><u>Back to Login</u></a>
                </div>
              </div>
              <div class="peer">
                <button id="reset" class="btn btn-primary">Reset Password</button>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </body>
	<script src="{{url('assets')}}/js/core/jquery.3.2.1.min.js"></script>
  <script src="{{url('assets')}}/js/plugin/sweetalert/sweetalert.min.js"></script>
  <script src="{{url('assets')}}/js/plugin/jquery.validate.min.js"></script> 
  <script src="{{url('assets')}}/js/plugin/jquery.form.min.js"></script> 
  <script src="{{url('assets')}}/_custom/js/script.js"></script> 
  <script>
    $(document).ready(function(){
      $(".reset-page").hide();
    });
    $(".go-reset").click(function(e){
      e.preventDefault();
      $(".login-page").hide();
      $(".reset-page").show();
    });
    $(".go-login").click(function(e){
      e.preventDefault();
      $(".login-page").show();
      $(".reset-page").hide();
    });
    $('#signin').click(function(e) {
        e.preventDefault();
        var btn = $(this);
        var form = $(this).closest('form');

        form.validate({
            rules: {
                username: {
                    required: true
                },
                password: {
                    required: true
                }
            }
        });
        if (!form.valid()) {
            return;
        }
        apiLoading(true, btn);            
        // return false;
        form.ajaxSubmit({
          url : "{{url('login/auth')}}",
          data: { _token: "{{ csrf_token() }}" },
          type: 'POST',
          success: function(response) {
            apiLoading(false, btn);
            apiRespone(response,
              function(res){
                if(res.api_status == 1){
                  localStorage.setItem("jwt_token", response.jwt_token);
                }
              },
              () => {
                window.location = "{{Request::session()->get('last_url')}}";
              }
            );
          },
          error: function(error){
            apiLoading(false, btn);
            swal(error.statusText);
          }
        });
    });
    
    $('#reset').click(function(e) {
        e.preventDefault();
        var btn = $(this);
        var form = $(this).closest('form');

        form.validate({
            rules: {
                username: {
                    required: true
                },
            }
        });
        if (!form.valid()) {
            return;
        }
        apiLoading(true, btn);            
        // return false;
        form.ajaxSubmit({
          url : "{{url('login/reset')}}",
          data: { _token: "{{ csrf_token() }}" },
          type: 'POST',
          success: function(response) {
            apiLoading(false, btn);
            apiRespone(response);
          },
          error: function(error){
            apiLoading(false, btn);
            swal(error.statusText);
          }
        });
    });
  </script>
</html>
