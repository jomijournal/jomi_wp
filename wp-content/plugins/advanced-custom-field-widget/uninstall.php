<?php

if (defined("WP_UNINSTALL_PLUGIN")==TRUE)
{
	if (current_user_can(activate_plugins))
	{
		delete_option("widget_adv_custom_field");
	}
}

?>