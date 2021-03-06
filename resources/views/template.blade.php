<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>{{isset($title) ? env('APP_NAME') . ' | '.$title  : env('APP_NAME')}}</title>

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
      .loader {
        border: 16px solid #f3f3f3; /* Light grey */
        border-top: 16px solid #3498db; /* Blue */
        border-radius: 50%;
        width: 120px;
        height: 120px;
        animation: spin 2s linear infinite;
      }

      @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
      }
      /* .table-responsive {
        display: block;
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        -ms-overflow-style: -ms-autohiding-scrollbar; 
      }*/
      
      .mapboxgl-popup-content{
          padding: 20;
          width: 280px;
      }
      .marker{
          color: black;
      }
    </style>
    <link rel="stylesheet" href="{{url('assets')}}/select2/css/select2.min.css">
    <link href="https://cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css"  rel="stylesheet">
    
    <link rel="stylesheet" href="{{url('assets')}}/_custom/css/style.css"> 
    <link rel="stylesheet" href="{{url('assets')}}/datetimepicker/css/bootstrap-datetimepicker.min.css">
    
    <script src="{{url('assets')}}/js/core/jquery.3.2.1.min.js"></script>
    <script src="{{url('assets')}}/js/plugin/sweetalert/sweetalert.min.js"></script>
    <script src="{{url('assets')}}/js/plugin/jquery.validate.min.js"></script> 
    <script src="{{url('assets')}}/js/plugin/jquery.form.min.js"></script> 
    <script src="{{url('assets')}}/_custom/js/script.js"></script> 
      
    
    <script type="text/javascript" src="{{url('vendor.js')}}"></script>
    <script type="text/javascript" src="{{url('bundle.js')}}"></script>

    <script src="{{url('assets')}}/js/plugin/datatables/datatables.min.js"></script>
    <script src="{{url('assets')}}/select2/js/select2.full.min.js"></script> 
    <script src="https://cdn.jsdelivr.net/npm/moment@2.27.0/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.27.0/locale/id.js"></script>
    <script src="{{url('assets')}}/datetimepicker/js/bootstrap-datetimepicker.min.js"></script> 
    <link rel="stylesheet" href="{{url('assets')}}/datetimepicker/css/bootstrap-datetimepicker-standalone.css">
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

    <!-- @App Content -->
    <!-- =================================================== -->
    <div>
      <!-- #Left Sidebar ==================== -->
      <div class="sidebar">
        <div class="sidebar-inner">
          <!-- ### $Sidebar Header ### -->
          <div class="sidebar-logo">
            <div class="peers ai-c fxw-nw">
              <div class="peer peer-greed">
                <a class="sidebar-link td-n" href="index.html">
                  <div class="peers ai-c fxw-nw">
                    <div class="peer">
                      <div class="logo">
                        <img src="{{url('assets/static/images/logo.png')}}" alt="">
                      </div>
                    </div>
                    <div class="peer peer-greed">
                      <h5 class="lh-1 mB-0 logo-text">{{env('APP_NAME')}}</h5>
                    </div>
                  </div>
                </a>
              </div>
              <div class="peer">
                <div class="mobile-toggle sidebar-toggle">
                  <a href="" class="td-n">
                    <i class="ti-arrow-circle-left"></i>
                  </a>
                </div>
              </div>
            </div>
          </div>

          <!-- ### $Sidebar Menu ### -->
          <ul style="margin-top:30px" class="sidebar-menu scrollable pos-r">
            @foreach($menus as $key => $mn)
              @if(count($mn->sub_menu) > 0)
                <li class="nav-item dropdown">
                  <a class="dropdown-toggle" href="javascript:void(0);">
                    <span class="icon-holder">
                      <i style="color:{{$mn->color}};" class="{{$mn->ikon}}"></i>
                    </span>
                    <span class="title">{{$mn->nama}}</span>
                    <span class="arrow">
                      <i class="ti-angle-right"></i>
                    </span>
                  </a>
                  <ul class="dropdown-menu">
                    @foreach ($mn->sub_menu as $sm)
                    <li>
                      <a class='sidebar-link' href="{{url($sm->link_sub)}}">{{$sm->nama_sub}}</a>
                    </li>
                    @endforeach
                  </ul>
                </li>
              @else
                <li class="nav-item">
                  <a class='sidebar-link' href="{{url($sm->link)}}">
                    <span class="icon-holder">
                      <i style="color:{{$mn->color}};" class="{{$mn->ikon}}"></i>
                    </span>
                    <span class="title">{{$mn->nama}}</span>
                  </a>
                </li>
              @endif
            @endforeach
          </ul>
        </div>
      </div>

      <!-- #Main ============================ -->
      <div class="page-container">
        <!-- ### $Topbar ### -->
        <div class="header navbar">
          <div class="header-container">
            <ul class="nav-left">
              <li>
                <a id='sidebar-toggle' class="sidebar-toggle" href="javascript:void(0);">
                  <i class="ti-menu"></i>
                </a>
              </li>
            </ul>
            <ul class="nav-right">
              <li class="dropdown">
                <a href="" class="dropdown-toggle2 no-after peers fxw-nw ai-c lh-1" data-toggle="dropdown">
                  <div class="peer mR-10">
                    <img class="w-2r bdrs-50p" src="https://randomuser.me/api/portraits/men/10.jpg" alt="">
                  </div>
                  <div class="peer">
                    <span class="fsz-sm c-grey-900">{{Auth::user()->nama}}</span>
                  </div>
                </a>
                <ul class="dropdown-menu fsz-sm">
                  <li>
                    <a href="" id="ubah-password" class="d-b td-n pY-5 bgcH-grey-100 c-grey-700">
                      <i class="ti-settings mR-10"></i>
                      <span>Ubah Password</span>
                    </a>
                  </li>
                  <li role="separator" class="divider"></li>
                  <li>
                    <a href="{{url('home/logout')}}" class="d-b td-n pY-5 bgcH-grey-100 c-grey-700">
                      <i class="ti-power-off mR-10"></i>
                      <span>Logout</span>
                    </a>
                  </li>
                </ul>
              </li>
            </ul>
          </div>
        </div>

        <!-- ### $App Screen Content ### -->
        <main class='main-content bgc-grey-100'>
          <div id='mainContent'>
            {!!$contents!!}
          </div>
        </main>

        <!-- ### $App Screen Footer ### -->
        <footer class="bdT ta-c p-30 lh-0 fsz-sm c-grey-600">
          <span>Copyright © 2021 <a href="https://sideveloper.com" target='_blank' title="Colorlib">Sideveloper</a>. Theme designed by <a href="https://colorlib.com" target='_blank' title="Colorlib">Colorlib</a>. All rights reserved.</span>
        </footer>
      </div>
    </div>
    
    <!-- Modal -->
    <div class="modal fade" id="modal-ubah-password" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
          <form class="modal-content" id="form-history">
              <div class="modal-header">
                  <h5 class="modal-title" id="exampleModalLabel">Ubah Password</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                  </button>
        </div>
              <div class="modal-body">
          {!!Sideveloper::formInput('Password Lama', 'password', 'passlama')!!}
          {!!Sideveloper::formInput('Password Baru', 'password', 'passbaru1')!!}
          {!!Sideveloper::formInput('Ulangi Password Baru', 'password', 'passbaru2')!!}
              </div>
              <div class="modal-footer">
                  <button type="submit" id="exec-ubah" class="btn btn-primary"><i class="fa fa-send"></i>
                      Ubah</button>
              </div>
          </form>
      </div>
    </div>
  </body>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
  <script>
    
	$("#ubah-password").click(function(e){
		e.preventDefault();
		$("#modal-ubah-password").modal('show');
  });
  
	$('#exec-ubah').click(function(e) {
      e.preventDefault();
      var btn = $(this);
      var form = $(this).closest('form');
      form.validate({
          rules: {
              passlama: {
                  required: true
              },
              passbaru1: {
                  required: true
              },
              passbaru2: {
                  required: true
              },
          }
      });
      if (!form.valid()) {
          return;
      }
      apiLoading(true, btn);
      form.ajaxSubmit({
          url : "{{url('home/ubah-password')}}",
          data: { _token: "{{ csrf_token() }}" },
          type: 'POST',
          success: function(response) {
              apiLoading(false, btn);
              apiRespone(response, (res)=> {
                if(res.api_status == 1){
                  $("#passlama").val('');
                  $("#passbaru1").val('');
                  $("#passbaru2").val('');
                  $("#modal-ubah-password").modal('hide');
                }
              });
          },
          error: function(error){
              apiLoading(false, btn);
              swal(error.statusText);
          }
      });
  });
  $('.dropdown-toggle2').dropdown();
  $(".sidebar .sidebar-menu li a").on("click", function () {
    var t = $(this);
    t.parent().hasClass("open")
        ? t
              .parent()
              .children(".dropdown-menu")
              .slideUp(200, function () {
                  t.parent().removeClass("open");
              })
        : (t.parent().parent().children("li.open").children(".dropdown-menu").slideUp(200),
          t.parent().parent().children("li.open").children("a").removeClass("open"),
          t.parent().parent().children("li.open").removeClass("open"),
          t
              .parent()
              .children(".dropdown-menu")
              .slideDown(200, function () {
                  t.parent().addClass("open");
              }));
}),
    $(".sidebar")
        .find(".sidebar-link")
        .each(function (t, i) {
            $(i).removeClass("active");
        })
        .filter(function () {
            var t = $(this).attr("href");
            return ("/" === t[0] ? t.substr(1) : t) === window.location.pathname.substr(1);
        })
        .addClass("active"),
    $(".sidebar-toggle").on("click", function (t) {
        $(".app").toggleClass("is-collapsed"), t.preventDefault();
    }),
    $("#sidebar-toggle").click(function (t) {
        t.preventDefault(),
            setTimeout(function () {
                window.dispatchEvent(window.EVENT);
            }, 300);
    });

  </script>
</html>
