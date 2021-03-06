<?php
	
	namespace App\Helpers;
	
	use App;
	use Cache;
	use Config;
	use DB;
	use Excel;
	use File;
	use Hash;
	use Log;
	use Mail;
	use PDF;
	use Request;
	use Route;
	use Session;
	use Storage;
	use Schema;
	use Validator;
	use Auth;
	use Carbon;
	
	class Sideveloper
	{

		#CONFIG APLIKASI
		public static function config($parameter, $app = 'fuel-supply'){

			$config = array(
				'appname'        => 'UKPBJ Karimun',
				'logo'           => url('assets/_custom/img/logo.png'),
				'sidelogo'       => url('assets/_custom/img/logo.png'),
				'favicon'        => url('assets/_custom/img/favicon.jpg'),
				'menus'          => 'menus_fuelsupply',
				'access'         => 'access_fuelsupply',
				'namespace'      => 'Modules\FuelSupply\Http\Controllers',
				'nama_privilege' => self::getPrivilege(),
				'staff'          => [2],
				'avp'            => [3],
				'vp'             => [4],
				'supplier'       => [5],
				'customer'       => [6],
				'staffkeu'       => [7],
				'pel'            => [2, 3, 4, 7, 8],
				'contact'        => '031-3284275',
				'email'          => 'info@pel.co.id',
			);
			return $config[$parameter];
		}

		public static function sendMail($subject, $tujuan, $data, $lampiran = null){
			// if(url('') == 'http://localhost/laravel')
			// return false;
			
			try{
				Mail::send('email', $data, function ($message) use ($subject, $tujuan, $lampiran)
				{
					$message->subject($subject);
					$message->from('donotreply@femos.com', 'Aplikasi Femos');
					if($tujuan['to'])
					$message->to($tujuan['to']);

					if(@$tujuan['cc'])
					$message->cc($tujuan['cc']);

					if($lampiran)
					$message->attach($lampiran);
				});
				return['status' => 1,'message' => 'success'];
			}
			catch (Exception $e){
				self::createLog($e->getMessage(), null,  'error');
				return ['status' => 0,'message' => $e->getMessage()];
			}
		}
		public static function getPrivilege(){
			if(Auth::user())
			return DB::table('privileges')->where('id_privilege', Auth::user()->id_privilege)->value('nama_privilege');
		}

        #ROUTING
        public static function routeController($prefix, $controller, $namespace = null, $token = false)
		{
			
			$prefix = trim($prefix, '/') . '/';
			
			$namespace = ($namespace) ?: 'App\Http\Controllers';
			
			try {
				Route::get($prefix, ['uses' => $controller . '@getIndex', 'as' => $controller . 'GetIndex']);
				
				$controller_class = new \ReflectionClass($namespace . '\\' . $controller);
				$controller_methods = $controller_class->getMethods(\ReflectionMethod::IS_PUBLIC);
				$wildcards = '/{one?}/{two?}/{three?}/{four?}/{five?}';
				foreach ($controller_methods as $method) {
					if ($method->class != 'Illuminate\Routing\Controller' && $method->name != 'getIndex') {
						if (substr($method->name, 0, 3) == 'get') {
							$method_name = substr($method->name, 3);
							$slug = array_filter(preg_split('/(?=[A-Z])/', $method_name));
							$slug = strtolower(implode('-', $slug));
							$slug = ($slug == 'index') ? '' : $slug;
							if($token){
								Route::get($prefix . $slug . $wildcards, ['uses' => $controller . '@' . $method->name, 'as' => $controller . 'Get' . $method_name]);
							}else{
								Route::get($prefix . $slug . $wildcards, ['uses' => $controller . '@' . $method->name, 'as' => $controller . 'Get' . $method_name]);
							}
						} elseif (substr($method->name, 0, 4) == 'post') {
							$method_name = substr($method->name, 4);
							$slug = array_filter(preg_split('/(?=[A-Z])/', $method_name));
							if($token){
								Route::post($prefix . strtolower(implode('-', $slug)) . $wildcards, [
									'uses' => $controller . '@' . $method->name,
									'as' => $controller . 'Post' . $method_name,
								]);
							}else{
								Route::post($prefix . strtolower(implode('-', $slug)) . $wildcards, [
									'uses' => $controller . '@' . $method->name,
									'as' => $controller . 'Post' . $method_name,
								]);
							}
						}
					}
				}
			} catch (\Exception $e) {
			
			}
		}

		#GET MENU
		public static function getMenu($id_privilege){
			$menu_data = DB::table('menus')
				->distinct()->select('urutan','menus.id_menu','nama_menu as nama','link','ikon', 'color')
				->join('permissions','permissions.id_menu','=','menus.id_menu')
				->where('id_privilege',$id_privilege)
				->orderBy('urutan')->get();
			foreach($menu_data as $key => $mn){
				$from = DB::table('submenus')
					->select('menus.id_menu','submenus.id_sub_menu','submenus.nama_sub_menu as nama_sub','submenus.link as link_sub','submenus.urutan')
					->join('menus','menus.id_menu','=','submenus.id_menu')
					->whereRaw(DB::raw("menus.id_menu=$mn->id_menu"))
					->orderBy('submenus.urutan');
				$sub_menu_data = DB::table(DB::raw("({$from->toSql()}) sub"))
					->select('sub.*')
					// ->mergeBindings($from->getQuery())
					->join('permissions', function ($join) {
						$join->on('permissions.id_menu', '=', 'sub.id_menu')
							->on('permissions.id_sub_menu','=','sub.id_sub_menu');
					})
					->where('permissions.id_privilege',$id_privilege)->get();
				$menu_data[$key]->sub_menu = $sub_menu_data;
			}
			return $menu_data;
		}

		#GET ACCESS
		public static function getAccess($id_privilege){
			$menu = DB::table('permissions')
				->select('menus.link as link_menu','submenus.link as link_sub_menu')
				->leftJoin('submenus', 'submenus.id_sub_menu', '=', 'permissions.id_sub_menu')
				->join('menus', 'menus.id_menu', '=', 'permissions.id_menu')
				->orderBy('menus.id_menu','asc')
				->where('id_privilege', $id_privilege)->get();
			$access_menu     = array_filter(array_column($menu->toArray(),'link_menu'));
			$access_sub_menu = array_filter(array_column($menu->toArray(),'link_sub_menu'));
			$access = array_merge($access_menu,$access_sub_menu);
			$forbidden  = DB::table('menus')
				->select('submenus.link as link_sub_menu','menus.link as link_menu')
				->leftJoin('submenus', 'submenus.id_menu', '=', 'menus.id_menu')
				->whereNotIn('menus.link', $access)
				->orWhere(function ($query) use($access){
					$query->whereNotIn('submenus.link', $access);
				})->get();
			$sec_menu     = array_filter(array_column($forbidden->toArray(),'link_menu'));
			$sec_sub_menu = array_filter(array_column($forbidden->toArray(),'link_sub_menu'));
			$sec = array_merge($sec_menu,$sec_sub_menu);
			return array('access_list' => $access, 'forbidden_list' => $sec);
		}

		
		#VIEW TEMPLATE
		public static function load($template = '', $view = '' , $view_data = array(), $view_add = array())
		{   
			$set  = $view_data;
			$data = array_merge($set, $view_add);
			$data['contents'] = view($view, $view_data);
			$data['menus']    = Request::session()->get('menus');

			return view($template, $data);
		}

		/**
		 * SESSION
		 */

		public static function getSession($nama){
			return Request::session()->get($nama);
		}

		#PATH URL
		public static function storageUrl($path=''){
			return url('storage/app/'.$path);
		}

		#CUSTOM URL
		public static function customUrl($path=''){
			return url('/public/assets/custom/' . $path);
		}


		/**
		 * FORM TEMPLATE
		 */

		
		#VIEW
		public static function formView($label, $isi){
			return "<div class=\"form-group m-form__group\">
			<label>
				$label
			</label>
			<input type=\"text\" value=\"$isi\" class=\"form-control m-input m-input--pill\"  disabled>
		</div>";
		}

		#INPUT HIDDEN
		public static function formHidden($name, $value = ''){
			return "<input type=\"hidden\" name=\"$name\" id=\"$name\" value=\"$value\" />";
		}

		#INPUT
		public static function formInput($label, $type, $name, $value = '', $add=''){
			return "<div id=\"all-$name\" class=\"form-group m-form__group\">
                <label>
                    $label
                </label>
                <input name=\"$name\" id=\"$name\" type=\"$type\" value=\"$value\" class=\"form-control m-input m-input--pill\" placeholder=\"$label\" $add>
            </div>";
		}

		#INPUT
		public static function formText($label, $name, $value = '', $add=''){
			return "<div class=\"form-group m-form__group\">
                <label>
                    $label
				</label>
				<textarea class=\"form-control\" name=\"$name\" id=\"$name\" $add>$value</textarea>
            </div>";
		}

		#INPUT
		public static function formFile($label, $name, $add='', $msg ='', $file = ''){
			if($file)
			$filetext =  "(<a target=\"_blank\" href=\"".self::storageUrl($file)."\">File</a>)";
			else
			$filetext = '';
			$msg = $msg ? "<small class=\"form-text text-muted\">$msg $filetext</small>" : '';
			return "<div class=\"form-group m-form__group\">
                <label>
                    $label
                </label>
				<input name=\"$name\" id=\"$name\" type=\"file\" class=\"form-control-file\" $add>
				$msg
            </div>";
		}

		public static function formInputButton($label, $type, $name, $value_button, $value = '', $add=''){
			return "<div class=\"form-group m-form__group\">
                <label>
                    $label
				</label>
				<div class=\"input-group\">
					<input name=\"$name\" id=\"$name\" type=\"$type\" value=\"$value\" class=\"form-control m-input m-input--pill\" placeholder=\"$label\" $add>
					<div class=\"input-group-prepend\">
						<button class=\"btn btn-default btn-border $name-button\" type=\"button\">$value_button</button>
					</div>
				</div>
            </div>";
		}

		public static function defaultInput($label, $type, $name, $value = '', $add=''){
			return "<input name=\"$name\" id=\"$name\" type=\"$type\" value=\"$value\" class=\"form-control m-input m-input--pill\" style=\"width:100%\" placeholder=\"$label\" $add>";
		}
		
		#MAKE OPTION DATA
		public static function makeOption($data, $value, $name, $all = false){
			$res = [];
			if($all && count($data) > 1){
				$res[0]['value'] = '';
				$res[0]['name'] = '--- Pilih Semua ---';
				$all = true;
			}else{
				$all = false;
			}
			foreach($data as $key => $d){
				if($all){
					$res[$key + 1]['value'] = $d->$value;
					$res[$key + 1]['name'] = $d->$name;
				}else{
					$res[$key]['value'] = $d->$value;
					$res[$key]['name'] = $d->$name;
				}
			}
			
			return $res;
		}
		#SELECT
		public static function formSelect($label, $data = null, $name, $value = '', $add=''){
			$option = '';
			$selected = '';
			if($data){
				foreach($data as $d){
					$selected = $d['value'] == $value ? 'selected' : '';
					$option .= "<option value=\"$d[value]\" $selected>$d[name]</option>";
				}
			}
			return "<div class=\"form-group m-form__group\">
                <label>
                    $label
                </label><br/>
				<select class=\"form-control\" name=\"$name\" id=\"$name\" $add>
					$option
				</select>
            </div>";
		}
		
		#SELECT
		public static function formSelect2($label, $data = null, $name, $value = '', $add=''){
			$option = '';
			$selected = '';
			if($data){
				foreach($data as $d){
					$selected = $d['value'] == $value ? 'selected' : '';
					if($value = 'all'){
						$selected = 'selected';
					}
					$option .= "<option value=\"$d[value]\" $selected>$d[name]</option>";
				}
			}
			$id = str_replace("[]","",$name);
			return "<div class=\"form-group m-form__group\">
                <label>
                    $label
                </label><br/>
				<select name=\"$name\" id=\"$id\" $add>
					$option
				</select>
            </div>";
		}
		#SELECT
		public static function defaultSelect($label, $data = null, $name, $value = '', $add=''){
			$option = '';
			if($data){
				foreach($data as $d){
					$selected = $d['value'] == $value ? 'selected' : '';
					$option .= "<option value=\"$d[value]\" $selected>$d[name]</option>";
				}
			}
			return "
				<select class=\"form-control m-input m-input--pill\" name=\"$name\" id=\"$name\" $add>
					$option
				</select>";
		}

		#SUBMIT
		public static function formSubmit($label, $name, $icon=null){
			$icon = $icon ? "<i class=\"$icon\"></i>" : "";
			return "<div class=\"pull-right\"><button type=\"submit\" id=\"$name\" class=\"btn btn-primary\"><span id=\"label-$name\">$icon $label</span></button></div><br/><br/>";
		}

		#CANCEL
		public static function formSubmit2($label, $name, $icon=null){
			$icon = $icon ? "<i class=\"$icon\"></i>" : "";
			return "<div class=\"pull-right\">
			<button type=\"button\" id=\"cancel-$name\" class=\"btn btn-light\"><span id=\"label-cancel-$name\">Cancel</span></button>
			<button type=\"submit\" id=\"$name\" class=\"btn btn-primary\"><span id=\"label-$name\">$icon $label</span></button>
			</div><br/><br/>";
		}

		public static function breadcrumb($data){
			$view ='';
			foreach($data as $bc):
				if($bc['link'] == '#' || !isset($bc['link'])):
					$view .= "<li class=\"breadcrumb-item active\" aria-current=\"page\">$bc[title]</li>";
				else:
					$view .= "<li class=\"breadcrumb-item\"><a href=\"$bc[link]\">$bc[title]</a></li>";
				endif;
			endforeach;
			return "<nav class=\"mB-30\" aria-label=\"breadcrumb\">
				<ol class=\"breadcrumb\">
					<li class=\"breadcrumb-item\"><a href=\"".url('')."\">Home</a></li>
					$view
				</ol>";
		}
		public static function title($title){
			return "<h4 class=\"c-grey-900 mT-10\">$title</h4>";

		}
		/* =============== */

		public static function selfUrl($path=''){
			return url(Request::segment(1).'/'.$path);
		}

		public static function apiUrl($path=''){
			return url('api/'.$path);
		}

		/**
		 * MODEL
		 */

		public static function getAlat(){
			$data = DB::table('m_alat');
			return $data;
		}

		public static function getDinas(){
			$data = DB::table('m_dinas');
			if(Auth::user()->id_dinas)
				$data->where('id_dinas', Auth::user()->id_dinas);

			return $data;
		}
		
		public static function getPekerjaan($id = null){
			$id_pekerjaan = DB::table('transaksi')->pluck('id_pekerjaan');
			if($id){
				$data = DB::table('pekerjaan')
					->where(function($query) use($id, $id_pekerjaan){
						$query->whereNotIn('id_pekerjaan', $id_pekerjaan);
						$query->orWhere('id_pekerjaan', $id);
					});
			}else{
				$data = DB::table('pekerjaan')->whereNotIn('id_pekerjaan', $id_pekerjaan);
			}
			if(Auth::user()->id_dinas)
				$data->where('id_dinas', Auth::user()->id_dinas);

			return $data;
		}

		public static function getReport($id_alat, $start, $end){
			$query = DB::table('transaksi')
            ->select('id', 'm_alat.nama as nama_alat', 'tanggal','bbm_level','gps')
            ->join('m_alat', 'm_alat.id_alat', '=', 'transaksi.id_alat');

			$query->whereBetween('tanggal', [$start, $end]);

			if($id_alat)
				$query->where('transaksi.id_alat', $id_alat);

			return $query;
		}

		/**
		 * DATE 
		 * example: 2020-03-17 12:00:00
		 */

		
		#Selasa, 17 Maret 2019
		public static function getFullDate($date){
			date_default_timezone_set('Asia/Jakarta');
            $tanggal = self::getTanggal($date);
            $bulan   = self::bulan(self::getBulan($date));
            $tahun   = self::getTahun($date);
            return self::hari($tanggal) .', '.$tanggal.' '.$bulan.' '.$tahun;  
		}

		public static function dateFull($date){
			return Carbon::createFromFormat('Y-m-d H:i:s', $date)->formatLocalized('%A, %d %B %Y - %H:%M');
		}

		public static function date($date){
			return Carbon::parse($date)->isoFormat('D MMMM Y');
		}

		public static function defaultDate($date){
			return Carbon::parse($date)->formatLocalized('%Y-%m-%d');
		}
		public static function defaultDateTime($date){
			return Carbon::parse($date)->formatLocalized('%Y-%m-%d %H:%M');
		}
		public static function imaisDate($date){
			return Carbon::parse($date)->formatLocalized('%d-%m-%Y');
		}

		public static function datePeriode($date){
			return Carbon::createFromFormat('Ym',$date)->formatLocalized("%B %Y");
		}

		public static function dateFormat($date, $format = 'Y-m-d H:i:s'){
			return date($format, strtotime($date));
		}


		public static function getTanggal($date){
			return substr($date,8,2);
		}
		public static function getBulan($date){
			return substr($date,5,2);
		}
		public static function getTahun($date){
			return substr($date,0,4);
		}

		public static function getHour($date){
			return substr($date, 11,5);
		}

		public static function hari($date){
			$hari = date('D', strtotime($date));
			switch ($hari) {
				case 'Sun':
					return 'Minggu';
					break;
				case 'Mon':
					return 'Senin';
					break;
				case 'Tue':
					return 'Selasa';
					break;
				case 'Wed':
					return 'Rabu';
					break;
				case 'Thu':
					return 'Kamis';
					break;
				case 'Fri':
					return 'Jumat';
					break;
				case 'Sat':
					return 'Sabtu';
					break;
			}
		}

		public static function isPeriode($date){
			$bln = substr($date,5,2);
			$bulan = '';
			switch ($bln){
				case 1: 
					$bulan = "JAN";
					break;
				case 2:
					$bulan = "FEB";
					break;
				case 3:
					$bulan = "MAR";
					break;
				case 4:
					$bulan = "APR";
					break;
				case 5:
					$bulan = "MEI";
					break;
				case 6:
					$bulan = "JUN";
					break;
				case 7:
					$bulan = "JUL";
					break;
				case 8:
					$bulan = "AGU";
					break;
				case 9:
					$bulan = "SEP";
					break;
				case 10:
					$bulan = "OKT";
					break;
				case 11:
					$bulan = "NOV";
					break;
				case 12:
					$bulan = "DES";
					break;
			}
			if(substr($date,8,2) > 14){
				return $bulan .'-2';
			}else{
				return $bulan .'-1';
			}

		}
		public static function getNomor($date){
			if(substr($date,8,2) > 14){
				return '2';
			}else{
				return '1';
			}
		}
		public static function bulan($bln){
			switch ($bln){
				case 1: 
					return "Januari";
					break;
				case 2:
					return "Februari";
					break;
				case 3:
					return "Maret";
					break;
				case 4:
					return "April";
					break;
				case 5:
					return "Mei";
					break;
				case 6:
					return "Juni";
					break;
				case 7:
					return "Juli";
					break;
				case 8:
					return "Agustus";
					break;
				case 9:
					return "September";
					break;
				case 10:
					return "Oktober";
					break;
				case 11:
					return "November";
					break;
				case 12:
					return "Desember";
					break;
			}
		} 

		public static function rupiah_format($nilai){
			return 'Rp. '.number_format($nilai,2);
		}

		public static function createLog($errors, $note = null,  $type = 'info'){
			$ip      = Request::ip();
			$input   = json_encode(Request::input());
			$url     = Request::url();
			$message = is_array($errors) ? json_encode($errors) : $errors;
			$user    = Auth::user() ? Auth::user()->username : '';
			$method  = Request::getMethod();
			$text    = "[IP: ". $ip. "] [USER: ".$user."] [URL: ".$url."] [METHOD: ".$method."] [PARAMETER: ".$input."] [MESSAGE: ".$message."] [KETERANGAN: ".$note."]";

			switch ($type) {
				case 'info':
					Log::info($text);
					break;

				case 'emergency':
					Log::emergency($text);
					break;
					
				case 'alert':
					Log::alert($text);
					break;

				case 'critical':
					Log::critical($text);
					break;

				case 'error':
					Log::error($text);
				break;

				case 'warning':
					Log::warning($text);
					break;

				case 'notice':
					Log::notice($text);
					break;
					
				case 'debug':
					Log::debug($text);
					break;
				default:
					# code...
					break;
			}
		}
		public static function dmsTodecimal($dms, $EastingAndNorthing)
		{
			$split = preg_split("/\./", $dms);
			$strLength = strlen($split[0]);
			if($strLength == 4)
			{
				// 0656.19158071924282
				// lat
				$d = substr($dms, 0, 2);
				$m = substr($dms, 2, 2);
				$s = "0." . $split[1];
			}
			else if($strLength == 5)
			{
				// long
				$d = substr($dms, 0, 3);
				$m = substr($dms, 3, 2);
				$s = "0." . $split[1];
			}
			else
			{
				throw new Exception("Invalid DMS format for '$dms'");    
			}
			// echo $d . ' -- '. $m. ' -- '.$s;die();
			$dec = $d + ($m/60) + (($s*60)/3600);
			
			if((strncmp($EastingAndNorthing, "S", 1) == 0) || (strncmp($EastingAndNorthing, "W", 1) == 0))
			{
				$dec = -1 * $dec;
			}
			
			return $dec;
		}
		public static function beliefmedia_dec_dms($dec) {
			$vars = explode('.', $dec);
			// -6.938893922629532
			//-0938893922629532
			$deg = $vars[0];
			$tempma = '0.' . $vars[1];
			$tempma = $tempma * 3600;
			$min = strval($tempma / 60);
			$sec = explode('.',$min)[1];
			$sec2 = $tempma - (floor($tempma / 60)) ;
			return array('deg' => $deg, 'min' => $min, 'sec' => $sec, 'sec2' => $sec2);
		}
		public static function decimalTodms($lat, $lng){
			$latpos = (strpos($lat, '-') !== false) ? 'S' : 'N';
			$lat = self::beliefmedia_dec_dms($lat);
			$lngpos = (strpos($lng, '-') !== false) ? 'W' : 'E';
			$lng = self::beliefmedia_dec_dms($lng);
			// return $lat;
			if(strlen($lat['min'])<2){
				$latmin = '0'.$lat['min'];
			}else{
				$latmin = $lat['min'];
			}
			if(strlen($lng['min'])<2){
				$lngmin = '0'.$lng['min'];
			}else{
				$lngmin = $lng['min'];
			}
			$latp = sprintf("%02d", abs($lat['deg'])).$latmin;
			$lngp = sprintf("%03d", abs($lng['deg'])).$lngmin;
			// return $lat;
			// '$GPGGA,181908.00,33524.2600000000002,S,1511226.352,E,4,13,1.00,495.144,M,29.200,M,0.10,0000*40';
			// '$GPGGA,181908.00,3404.7041778,N,07044.3966270,W,4,13,1.00,495.144,M,29.200,M,0.10,0000*40';
			// $GPGGA,181908.00,0656.18,S,110025.29,E,4,13,1.00,495.144,M,29.200,M,0.10,0000*40
			// $GPGGA,181908.00,656.19,S,11025.28,E,4,13,1.00,495.144,M,29.200,M,0.10,0000*40
			return '$GPGGA'.",181908.00,$latp,$latpos,$lngp,$lngpos,4,13,1.00,495.144,M,29.200,M,0.10,0000*40";
			// return $latpos . abs($lat['deg']) . '&deg;' . $lat['min'] . '&apos;' . $lat['sec'] . '&quot ' . $lngpos . abs($lng['deg']) . '&deg;' . $lng['min'] . '&apos;' . $lng['sec'] . '&quot';
		}

		public static function parseNMEA($nmea){
			$exp = explode(',', $nmea);
			$result['lat'] = self::dmsTodecimal($exp[2], $exp[3]);
			$result['lng'] = self::dmsTodecimal($exp[4], $exp[5]);
			return $result;
		}
    }
