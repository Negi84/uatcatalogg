<?php
/**
 * @var \abc\core\view\AView $this
 */
use abc\core\ABC; ?>
    <meta charset="utf-8">
    <title><?php echo $title; ?></title>
    <base href="<?php echo $base; ?>"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">

<?php foreach ($links as $link) { ?>
    <link href="<?php echo $link['href']; ?>" rel="<?php echo $link['rel']; ?>"/>
<?php } ?>

<?php if ( is_file( ABC::env('DIR_RESOURCES') . $icon ) ) {  ?>
    <link href="resources/<?php echo $icon; ?>" type="image/png" rel="icon" />
<?php } ?>

    <script type="text/javascript"
            src="<?php echo $ssl ? 'https' : 'http'?>://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script type="text/javascript">
		if (typeof jQuery == 'undefined') {
			var include = '<script type="text/javascript" src="<?php echo $this->templateResource('assets/js/jquery/jquery-1.12.4.min.js'); ?>"><\/script>';
			document.write(include);
		}
        <?php if($retina){?>
		if((window.devicePixelRatio===undefined?1:window.devicePixelRatio)>1) {
			document.cookie = 'HTTP_IS_RETINA=1;path=/';
		}
        <?php } ?>
    </script>
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Material+Icons" />
	<link rel="stylesheet" type="text/css" href="<?php echo $this->templateResource('assets/css/vuetify/v2/vuetify.css'); ?>" />
	<link href="https://cdn.jsdelivr.net/npm/@mdi/font@4.x/css/materialdesignicons.min.css" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, minimal-ui">

	<script type="text/javascript" src="<?php echo $this->templateResource('assets/js/vue/vue.js'); ?>"></script>
	<script type="text/javascript" src="<?php echo $this->templateResource('assets/js/tinymce-vue/tinymce-vue.js'); ?>"></script>
	<script type="text/javascript" src="<?php echo $this->templateResource('assets/js/vuetify/v2/vuetify.js'); ?>"></script>
	<script type="text/javascript" src="<?php echo $this->templateResource('assets/js/vee-validate/vee-validate.js'); ?>"></script>
	<script type="text/javascript" src="<?php echo $this->templateResource('assets/js/polyfill/polyfill.min.js'); ?>"></script>
	<script type="text/javascript" src="<?php echo $this->templateResource('assets/js/axios/axios.min.js'); ?>"></script>
	<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/lodash@4.13.1/lodash.min.js"></script>
    <script type="text/javascript" src="<?php echo $this->templateResource('assets/js/jquery/jquery-migrate-1.2.1.min.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo $this->templateResource('assets/js/jquery/jquery.cookies.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo $this->templateResource('assets/js/jquery/jquery-ui/jquery-ui-1.10.4.custom.min.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo $this->templateResource('assets/js/bootstrap.min.js'); ?>"></script>
	<link rel="stylesheet" type="text/css" href="vendor/css/font-awesome/css/fontawesome-all.min.css" />
    <script defer type="text/javascript" src="<?php echo $this->templateResource('assets/js/tinymce/tinymce.min.js'); ?>"></script>
<?php foreach ($scripts as $script) { ?>
    <script type="text/javascript" src="<?php echo $script; ?>"></script>
<?php } ?>
    <script type="text/javascript" src="<?php echo $this->templateResource('assets/js/aform.js'); ?>"></script>
    <link rel="stylesheet" type="text/css" href="<?php echo $this->templateResource('assets/css/stylesheet.css'); ?>" />
<?php foreach ($styles as $style) { ?>
    <link rel="<?php echo $style['rel']; ?>" type="text/css" href="<?php echo $style['href']; ?>"
          media="<?php echo $style['media']; ?>"/>
<?php } ?>
<?php
//Generic PHP processed Javascript section
if (is_file(ABC::env('DIR_TEMPLATES') . 'default/js/tinymce/langs/' . $language_locale . '.js')) {
    $mce_lang_code = $language_locale;
} elseif (is_file(ABC::env('DIR_TEMPLATES') . 'default/js/tinymce/langs/' . substr($language_locale, 0, 2) . '.js')) {
    $mce_lang_code = substr($language_locale, 0, 2);
}else{
    $mce_lang_code = 'en';
}
?>
    <script type="text/javascript">
		//define tinymce config
		var mcei = {
			language: "<?php echo $mce_lang_code; ?>",
			formats: {
				alignleft: [{
					selector: "p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li",
					styles: {textAlign: "left"}
				}, {selector: "img,table,dl.wp-caption", classes: "alignleft"}],
				aligncenter: [{
					selector: "p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li",
					styles: {textAlign: "center"}
				}, {selector: "img,table,dl.wp-caption", classes: "aligncenter"}],
				alignright: [{
					selector: "p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li",
					styles: {textAlign: "right"}
				}, {selector: "img,table,dl.wp-caption", classes: "alignright"}],
				strikethrough: {inline: "del"}
			},
			forced_root_block : false,
			cleanup : false,
			verify_html : false,
			trim_span_elements: false,
			fix_list_elements: false,
			relative_urls: false,
			remove_script_host: false,
			convert_urls: false,
			browser_spellcheck: true,
			entities: "38,amp,60,lt,62,gt",
			entity_encoding: "raw",
			keep_styles: false,
			cache_suffix: "abc-mce-433-20160114",
			preview_styles: "font-family font-size font-weight font-style text-decoration text-transform",
			end_container_on_empty_block: true,
			editimage_disable_captions: false,
			editimage_html5_captions: true,
			plugins: "charmap,colorpicker,hr,lists,media,paste,tabfocus,textcolor,fullscreen,link,table",
			resize: true,
			menubar: false,
			autop: true,
			indent: false,
			toolbar: [
				'undo | redo | formatselect | fontselect | fontsizeselect | bold | italic | strikethrough | forecolor | backcolor',
				'numlist | bullist | blockquote | hr | alignleft | aligncenter | alignright | link | spellchecker | dfw | fullscreen | table'
			],
			selector: '',
			valid_elements : '*[*]',
			valid_children : "+body[style]",
			extended_valid_elements:'script[language|type|src]',
			invalid_elements : "...",
			tabfocus_elements: "content-html,save-post",
			body_class: "content post-type-post post-status-auto-draft post-format-standard locale-en-gb",
			autoresize_on: true,
			add_unload_trigger: false,
			height: 330
		};
		$(document).ready(function () {
			//system check warnings
            <?php if($system_error) { ?>
			error_alert(<?php abc_js_echo($system_error); ?>, false);
            <?php } ?>
            <?php if($system_warning) { ?>
			warning_alert(<?php abc_js_echo($system_warning); ?>, false);
            <?php } ?>
            <?php if($system_notice) { ?>
			info_alert(<?php abc_js_echo($system_notice); ?>, false);
            <?php } ?>
			numberSeparators = {decimal:'<?php echo $decimal_point; ?>', thousand:'<?php echo $thousand_point; ?>'};
		});
		//periodical updater of new message notifier
		var growl = null;
		var alertcount = 3;
		var system_checker = function () {
			if(alertcount <= 0) {
				return;
			}
			$.ajax({
				async: false,
				cache: false,
				url: '<?php echo $system_checker_url?>',
				success: function(data) {
					if(data != null && data != undefined) {
						if(growl != null && growl != undefined ) {
							growl.close();
						}
						growl = showSystemAlert(data);
					}
				},
				complete: function() {
					// Schedule the next request when the current one's complete
					alertcount--;
					setTimeout(system_checker, 600000);
				}
			});
		};
		var showSystemAlert = function(data){
			if(data.hasOwnProperty('error')){
				return error_alert(data.error, false);
			}
			if(data.hasOwnProperty('warning')){
				return warning_alert(data.warning, false);
			}
			if(data.hasOwnProperty('notice')){
				return info_alert(data.notice, true);
			}
			return;
		}
		var wrapConfirmDelete = function(){
			var wrapper = '<div class="btn-group dropup" />';
			var popover, href;
			$('a[data-confirmation="delete"]').each( function(){
				if($(this).attr('data-toggle')=='dropdown' ){ return;}
				var action = $(this).attr('onclick');
				if ( action ) {
					action = 'onclick="'+action+'"';
				} else {
					href = $(this).attr('href');
					if(!href){ return;}
					if(href.length==0 || href=='#'){ return;}
					action = 'href="' + href +'"';
				}
				var conf_text = $(this).attr('data-confirmation-text');
				if (!conf_text) {
					conf_text = <?php abc_js_echo($text_confirm); ?>;
				}
				$(this).wrap(wrapper);
				popover = '<div class="confirm_popover dropdown-menu dropdown-menu-right alert alert-danger" role="menu">'+
					'<h5 class="center">'+ conf_text +'</h5>'+
					'<div class="center">'+
					'<a class="btn btn-danger" '+action+' ><i class="fa fa-trash-o"></i>&nbsp;<?php echo $text_yes;?></a>&nbsp;&nbsp;'+
					'<a class="btn btn-default"><i class="fa fa-undo"></i>&nbsp;<?php echo $text_no;?></a>'+
					'</div>'+
					'</div>';
				$(this).after(popover);
				$(this).attr('onclick','return false;');
				$(this).attr('data-toggle','dropdown').addClass('dropdown-toggle');
			});
		}
		$(document).on('change', wrapConfirmDelete);
		//periodical updater of new message notifier
		var noticecount = 3;
		var notifier_updater = function () {
			if(noticecount <= 0) {
				return;
			}
			$.ajax({
				url: '<?php echo $notifier_updater_url?>',
				success: buildNotifier,
				complete: function() {
					// Schedule the next request when the current one's complete
					noticecount--;
					setTimeout(notifier_updater, 600000);
				},
				error:function (jqXHR, textStatus, errorThrown) {
					console.log('notifier_updater: '+textStatus + ": " + errorThrown);
				}
			});
		}
		var buildNotifier = function(data){
			$('.new_messages .badge').html(data.total);
			$('.new_messages .dropdown-menu-head h5.title').html(data.total_title);
			var  list = $('.new_messages ul.dropdown-list.gen-list');
			list.html('');
			var html = '';
			var mes;
			for(var k in data.shortlist){
				mes = data.shortlist[k];
				var iconclass='';
				var badgeclass='';
				if(mes.status=='N'){
					iconclass = 'fa-info';
					badgeclass = 'success';
				}else if(mes.status=='W' || mes.status=='E'){
					iconclass = 'fa-warning';
					badgeclass = 'danger';
				}
				html = '<li '+ (mes.viewed<1 ? 'class="new"': '')+ '>' ;
				html += '<a class="message-'+badgeclass+'" href="'+mes.href+'" data-toggle="modal" data-target="#message_modal"><span class="thumb"><p class="fa ' + iconclass + ' fa-3 '+badgeclass+'"></p></span>';
				html += '<span class="desc"><span class="name">'+mes.title + (mes.viewed<1 ? '<span class="badge badge-'+badgeclass+'">new</span>': '')+'</span>';
				html += '<span class="msg">'+mes.message +'</span></span></a></li>';
				list.append(html);
			}
			list.append('<li class="new"><a href="<?php echo $message_manager_url; ?>"><?php echo $text_read_all_messages; ?></a></li>');
		}
        <?php if($this->user->isLogged()){?>
		$(document).ready(function(){
			notifier_updater();
			system_checker();
			$('#message_modal').on('hide.bs.modal', notifier_updater );
            <?php
            //do ajax call to check extension updates
            if($check_updates_url){ ?>
			$.get('<?php echo $check_updates_url?>');
            <?php }?>
		});
        <?php } ?>
    </script>
<?php
//NOTE: More JS loaded in page.tpl. This is to improve performance. Do not move above to page.tpl
?>