<?php
/**
Plugin Name: Static Blocks
Description: Cms Static Blocks
Version: 1.0
Author: Sergey Cherepanov
Author URI: http://www.cherepanov.org.ua
*/

class StaticBlock
{
    static function init()
    {
        $labels = array(
            'name' => _x('Static Blocks', 'post type general name'),
            'singular_name' => _x('Static Block', 'post type singular name'),
            'add_new' => _x('Add new', 'Block'),
            'add_new_item' => __('Add new'),
            'edit_item' => __('Edit'),
            'new_item' => __('New Static Block'),
            'view_item' => __('Static Block Info'),
            'search_items' => __('Search'),
            'not_found' => __('Not Found'),
            'not_found_in_trash' => __('Not Found in trash'),
            'parent_item_colon' => ''
        );

        register_post_type('static_block',
            array(
                'labels' => $labels,
                'public' => true,
                'supports' => array('title', 'editor'),
            )
        );
    }

    static public function init_meta_boxes()
    {
        add_meta_box('identifier', 'Identifier', array('StaticBlock', 'meta_box_control'), 'static_block', 'normal', 'high');
    }

    static public function meta_box_control($post)
    {
        ?>
        <label>Identifier:<input type="text" value="<?php echo get_post_meta($post->ID, '_identifier', true) ?>"
                                 name="identifier"/></label>
        <?php
    }

    static public function save($post_ID)
    {
        if (isset($_POST['identifier'])) {
            if ($sku = trim($_POST['identifier'])) {
                update_post_meta($post_ID, '_identifier', $sku);
            } else {
                delete_post_meta($post_ID, '_identifier');
            }
        }
    }
}

add_action('init', array('StaticBlock', 'init'));

if (is_admin()) {
    add_action('load-post.php', array('StaticBlock', 'init_meta_boxes'));
    add_action('load-post-new.php', array('StaticBlock', 'init_meta_boxes'));
    add_action('edit_post', array('StaticBlock', 'save'));
}

function get_product_identifier($post = null)
{
    if (!$post) {
        global $post;
    }
    return get_post_meta($post->ID, '_identifier', true);
}

/**
 * @param string|int $identifier
 * @return array|bool
 */
function get_static_blocks_by_id($identifier)
{
    /** @var $wpdb wpdb */
    global $wpdb;
    if (!$table = _get_meta_table('post')) {
        return false;
    }
    $postIds = $wpdb->get_col($wpdb->prepare("SELECT `post_id` FROM $table WHERE `meta_key` = '_identifier' AND `meta_value` = %s", $identifier));
    if (empty($postIds)) {
        return false;
    }

    return get_posts(array('include' => $postIds, 'post_type' => 'static_block'));
}

/**
 * @param int $identifier
 * @return string
 */
function get_block_content($identifier)
{
    $result = '';
    $blocks = get_static_blocks_by_id($identifier);
    if ($blocks) {
        foreach ($blocks as $block) {
            if ($block->post_content) {
                $result .= apply_filters('the_content', $block->post_content);
            }
        }
    }
    return $result;
}
