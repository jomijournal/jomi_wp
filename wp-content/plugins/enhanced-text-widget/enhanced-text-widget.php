<?php
/*
Plugin Name: Enhanced Text Widget
Plugin URI: http://wordpress.org/plugins/enhanced-text-widget/
Description: An enhanced version of the default text widget where you may have Text, HTML, CSS, JavaScript, Flash, and/or PHP as content with linkable widget title.
Version: 1.4.3
Author: Boston Dell-Vandenberg
Author URI: http://pomelodesign.com/
License: GPL2

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class EnhancedTextWidget extends WP_Widget {

    /**
     * Widget construction
     */
    function __construct() {
        $widget_ops = array('classname' => 'widget_text enhanced-text-widget', 'description' => __('Text, HTML, CSS, PHP, Flash, JavaScript, Shortcodes', 'enhancedtext'));
        $control_ops = array('width' => 400, 'height' => 350);
        parent::__construct('EnhancedTextWidget', __('Enhanced Text', 'enhancedtext'), $widget_ops, $control_ops);
        load_plugin_textdomain('enhancedtext', false, basename( dirname( __FILE__ ) ) . '/languages' );
    }

    /**
     * Setup the widget output
     */
    function widget( $args, $instance ) {

        if (!isset($args['widget_id'])) {
          $args['widget_id'] = null;
        }

        extract($args);

        $title = apply_filters('widget_title', empty($instance['title']) ? '' : $instance['title'], $instance);
        $titleUrl = empty($instance['titleUrl']) ? '' : $instance['titleUrl'];
        $cssClass = empty($instance['cssClass']) ? '' : $instance['cssClass'];
        $text = apply_filters('widget_enhanced_text', $instance['text'], $instance);
        $hideTitle = !empty($instance['hideTitle']) ? true : false;
        $newWindow = !empty($instance['newWindow']) ? true : false;
        $filterText = !empty($instance['filter']) ? true : false;
        $bare = !empty($instance['bare']) ? true : false;

        if ( $cssClass ) {
            if( strpos($before_widget, 'class') === false ) {
                $before_widget = str_replace('>', 'class="'. $cssClass . '"', $before_widget);
            } else {
                $before_widget = str_replace('class="', 'class="'. $cssClass . ' ', $before_widget);
            }
        }

        echo $bare ? '' : $before_widget;

        if ($newWindow) $newWindow = "target='_blank'";

        if(!$hideTitle && $title) {
            if($titleUrl) $title = "<a href='$titleUrl' $newWindow>$title</a>";
            echo $bare ? $title : $before_title . $title . $after_title;
        }

        echo $bare ? '' : '<div class="textwidget widget-text">';

        // Parse the text through PHP
        ob_start();
        eval('?>' . $text);
        $text = ob_get_contents();
        ob_end_clean();

        // Run text through do_shortcode
        $text = do_shortcode($text);

        // Echo the content
        echo $filterText ? wpautop($text) : $text;

        echo $bare ? '' : '</div>' . $after_widget;

    }

    /**
     * Run on widget update
     */
    function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        if ( current_user_can('unfiltered_html') )
            $instance['text'] =  $new_instance['text'];
        else
            $instance['text'] = wp_filter_post_kses($new_instance['text']);
        $instance['titleUrl'] = strip_tags($new_instance['titleUrl']);
        $instance['cssClass'] = strip_tags($new_instance['cssClass']);
        $instance['hideTitle'] = isset($new_instance['hideTitle']);
        $instance['newWindow'] = isset($new_instance['newWindow']);
        $instance['filter'] = isset($new_instance['filter']);
        $instance['bare'] = isset($new_instance['bare']);

        return $instance;
    }

    /**
     * Setup the widget admin form
     */
    function form( $instance ) {
        $instance = wp_parse_args( (array) $instance, array(
            'title' => '',
            'titleUrl' => '',
            'cssClass' => '',
            'text' => ''
        ));
        $title = $instance['title'];
        $titleUrl = $instance['titleUrl'];
        $cssClass = $instance['cssClass'];
        $text = format_to_edit($instance['text']);
?>

        <style>
            .monospace { font-family: Consolas, Lucida Console, monospace; }
        </style>

        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'enhancedtext'); ?>:</label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('titleUrl'); ?>"><?php _e('URL', 'enhancedtext'); ?>:</label>
            <input class="widefat" id="<?php echo $this->get_field_id('titleUrl'); ?>" name="<?php echo $this->get_field_name('titleUrl'); ?>" type="text" value="<?php echo $titleUrl; ?>" />
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('cssClass'); ?>"><?php _e('CSS Classes', 'enhancedtext'); ?>:</label>
            <input class="widefat" id="<?php echo $this->get_field_id('cssClass'); ?>" name="<?php echo $this->get_field_name('cssClass'); ?>" type="text" value="<?php echo $cssClass; ?>" />
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('text'); ?>"><?php _e('Content', 'enhancedtext'); ?>:</label>
            <textarea class="widefat monospace" rows="16" cols="20" id="<?php echo $this->get_field_id('text'); ?>" name="<?php echo $this->get_field_name('text'); ?>"><?php echo $text; ?></textarea>
        </p>

        <p>
            <input id="<?php echo $this->get_field_id('hideTitle'); ?>" name="<?php echo $this->get_field_name('hideTitle'); ?>" type="checkbox" <?php checked(isset($instance['hideTitle']) ? $instance['hideTitle'] : 0); ?> />&nbsp;<label for="<?php echo $this->get_field_id('hideTitle'); ?>"><?php _e('Do not display the title', 'enhancedtext'); ?></label>
        </p>

        <p>
            <input type="checkbox" id="<?php echo $this->get_field_id('newWindow'); ?>" name="<?php echo $this->get_field_name('newWindow'); ?>" <?php checked(isset($instance['newWindow']) ? $instance['newWindow'] : 0); ?> />
            <label for="<?php echo $this->get_field_id('newWindow'); ?>"><?php _e('Open the URL in a new window', 'enhancedtext'); ?></label>
        </p>

        <p>
            <input id="<?php echo $this->get_field_id('filter'); ?>" name="<?php echo $this->get_field_name('filter'); ?>" type="checkbox" <?php checked(isset($instance['filter']) ? $instance['filter'] : 0); ?> />&nbsp;<label for="<?php echo $this->get_field_id('filter'); ?>"><?php _e('Automatically add paragraphs to the content', 'enhancedtext'); ?></label>
        </p>

        <p>
            <input id="<?php echo $this->get_field_id('bare'); ?>" name="<?php echo $this->get_field_name('bare'); ?>" type="checkbox" <?php checked(isset($instance['bare']) ? $instance['bare'] : 0); ?> />&nbsp;<label for="<?php echo $this->get_field_id('bare'); ?>"><?php _e('Do not output before/after_widget/title', 'enhancedtext'); ?></label>
        </p>

        <p class="credits"><small><?php _e('Developed by', 'enhancedtext'); ?> <a href="http://pomelodesign.com">Pomelo Design</a></small></p>

<?php
    }
}

/**
 * Register the widget
 */
function enhanced_text_widget_init() {
    register_widget('EnhancedTextWidget');
}

add_action('widgets_init', 'enhanced_text_widget_init');
