/* Block Editor Script */

var current_item_class = 'current-item';
var main_editor = 'postdivrich';
var current_editor = main_editor;
var show_link = "Show / Hide Block Editor Meta";
var custom_meta_class = "be-block";

function be_init_menu()
{
	var be_block_exist = false;
	
	var be_meta_link = '<tr><td colspan="2"><p class="be-meta-link"><a href="#" id="be-toggle-block-content">' + show_link + '</a></p></td></tr>';
	
	var be_menu = '<div id="blockeditor-menu"><ul>'
		+ '<li><a href="#" rel="' + main_editor + '" class="' + current_item_class + '">Main Content</a></li>';

	jQuery('form#post div#post-body-content div.metaeditdiv').each(function(){
		
		be_menu += '<li><a href="#" rel="' + jQuery(this).attr("id") + '">' + jQuery(this).attr("data-blockname") + '</a></li>';
		jQuery('form#post div#postcustom tr#meta-' + jQuery(this).attr("data-metaid")).hide();
		jQuery('form#post div#postcustom tr#meta-' + jQuery(this).attr("data-metaid")).addClass(custom_meta_class);
		jQuery('form#post div#postcustom tr#meta-' + jQuery(this).attr("data-metaid")).attr("data-metaid", jQuery(this).attr("data-metaid"));
		be_block_exist = true;
	});
	
	be_menu += '</ul><div class="clear"></div></div>';
	
	jQuery('form#post div#post-body-content div#' + main_editor).before(be_menu);
	
	if(be_block_exist)
	{
		jQuery('form#post div#postcustom table#list-table tbody#the-list').prepend(be_meta_link);
	}
}

jQuery(document).ready( function() {
	
	be_init_menu();
	
	jQuery('form#post div#post-body-content div#blockeditor-menu li a').live('click', function(){
		
		var editor = jQuery(this).attr("rel");
		
		if( current_editor != editor )
		{
			jQuery('form#post div#post-body-content div#' + current_editor).hide();
			jQuery('form#post div#post-body-content div#blockeditor-menu li a[rel=' + current_editor + ']').removeClass(current_item_class);
			jQuery('form#post div#post-body-content div#' + editor).show();
			jQuery(this).addClass(current_item_class);
			
			current_editor = editor;
		}
		
		return false;
	});
	
	jQuery('form#post').submit(function(e){

		jQuery('form#post div#post-body-content div.metaeditdiv').each(function(){
			
			var meta_id = jQuery(this).attr("data-metaid");
			
			jQuery('a#metaeditor-' + meta_id + '-html', this).click();
			var meta_value = jQuery('textarea#metaeditor-'+meta_id, this).val();
			
			jQuery('form#post div#postcustom tr#meta-' + meta_id + ' textarea').val(meta_value);
			jQuery('form#post div#postcustom tr#meta-' + meta_id + ' input#meta-' + meta_id + '-submit').click();
		});
	})
	
	jQuery('form#post div#postcustom table#list-table tbody#the-list a#be-toggle-block-content').live('click', function(){
		
		jQuery('form#post div#postcustom tr.be-block').toggle();
		
		return false;
	});
	
	jQuery('form#post div#postcustom table#list-table tbody#the-list input.deletemeta[type=submit]').live('click', function(){
		
		var meta_id = jQuery(this).parent().parent().parent().attr("data-metaid");
		var editor = 'postdivmeta-' + meta_id;
		
		jQuery(this).parent().parent().parent().removeClass(custom_meta_class);
		jQuery('form#post div#post-body-content div#blockeditor-menu li a[rel=' + editor + ']').parent().hide();
		
		if(current_editor == editor)
		{
			jQuery('form#post div#post-body-content div#blockeditor-menu li a[rel=' + main_editor + ']').click();
		}	
		
	});
	
});