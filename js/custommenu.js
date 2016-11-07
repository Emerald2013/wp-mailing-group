jQuery(document).ready(function(){
	jQuery('#toplevel_page_mailinggroup_list').removeClass("wp-not-current-submenu");
	jQuery('#toplevel_page_mailinggroup_list').addClass("wp-has-current-submenu wp-menu-open");
	jQuery('#adminmenu').find("#toplevel_page_mailinggroup_list > a:first-child").removeClass("wp-not-current-submenu");
	jQuery('#adminmenu').find("#toplevel_page_mailinggroup_list > a:first-child").addClass("wp-has-current-submenu");
});