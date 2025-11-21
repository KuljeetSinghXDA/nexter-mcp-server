<?php
/**
 * TPGB Add Category Image.
 *
 * @package TPGBP
 * @since 3.1.3
 */
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'Tpgbp_Add_Category_Img' ) ) {
    class Tpgbp_Add_Category_Img {

		/**
		 *  Constructor
		 */
		function __construct() {
            add_action('admin_init', [$this, 'tpgb_all_texolist']);
		}

        function tpgb_all_texolist(){
            $taxonomies = get_taxonomies();
            // print_r(Tp_Blocks_Helper::tpgb_get_post_taxonomies());
            if ( $taxonomies ) {
                foreach ( $taxonomies  as $taxonomy ) {
                    $exclude = array( 'nxt_builder_category' , 'product_cat' , 'author' );
                    if( TRUE === in_array( $taxonomy, $exclude ) )
                        continue;
                    
                    add_action(''.$taxonomy.'_add_form_fields', [$this,'tpgb_category_thumbnail_field'], 10, 2);
                    add_action(''.$taxonomy.'_edit_form_fields',  array($this,'tpgb_category_thumbnail_field'), 10, 2);
                    add_action('edited_'.$taxonomy.'', array($this,'tpgb_category_thumbnail_save'), 10, 2);
                    add_action('create_'.$taxonomy.'', array($this,'tpgb_category_thumbnail_save'), 10, 2);	
        
                    add_filter( 'manage_edit-'.$taxonomy.'_columns', array($this,'tpgb_category_image_column' ) , 15 );
                    add_filter( 'manage_'.$taxonomy.'_custom_column', array($this ,'tpgb_category_image_column_value' ), 10, 3 );
                }
            }
        }

        /**
		* Image Field In Category edit
        * @since 2.0.9
		*/
        function tpgb_category_thumbnail_field($tag) {
            $thumbnail_id = ( isset($tag) && isset($tag->term_id) ) ? get_term_meta($tag->term_id, 'tpgb_category_id', true) : '' ;
               $thumbnail_url = wp_get_attachment_url($thumbnail_id);
            ?>
             <script>
                document.addEventListener('DOMContentLoaded', function() {
                    if (typeof wp.media !== 'undefined') {
                        var _custom_media = true;
                        var _orig_send_attachment = wp.media.editor.send.attachment;

                        document.querySelectorAll('.category-thumbnail').forEach(function(element) {
                            element.addEventListener('click', function(e) {
                                e.preventDefault();
                                var button = this;
                                var id = button.getAttribute('id').replace('_button', '');
                                _custom_media = true;
                                
                                wp.media.editor.send.attachment = function(props, attachment) {
                                    if (_custom_media) {
                                        document.getElementById('tpgb_category_id').value = attachment.id;
                                        document.getElementById('tpgb_taxonomy_image_preview').innerHTML = '<img src="' + attachment.url + '" style="max-width:150px;"/>';
                                    } else {
                                        return _orig_send_attachment.apply(this, [props, attachment]);
                                    }
                                };

                                wp.media.editor.open(button);
                                return false;
                            });
                        });

                        document.getElementById('category_thumbnail_remove_button').addEventListener('click', function(e) {
                            e.preventDefault();
                            document.getElementById('tpgb_category_id').value = '';
                            document.getElementById('tpgb_taxonomy_image_preview').innerHTML = '';
                        });
                    }
                });
            </script>
             <div class="form-field">
                <label for="category-thumbnail"><?php echo esc_html__('Thumbnail','tpgbp'); ?></label></th>
                <input type="hidden" id="tpgb_category_id" name="tpgb_category_id" value="<?php echo esc_attr($thumbnail_id); ?>">
                <div id="tpgb_taxonomy_image_preview"><img id="category_thumbnail_preview" src="<?php echo esc_url($thumbnail_url); ?>" style="max-width:150px;"/></div>
                <input id="category_thumbnail_button" type="button" class="button category-thumbnail" value="<?php echo esc_attr__('Add/Edit Image','tpgbp'); ?>" />
                <input id="category_thumbnail_remove_button" type="button" class="button" value="<?php echo esc_attr__('Remove','tpgbp'); ?>" />
            </div>
            <?php
        }
        
        /**
		* Save Image Data
        * @since 2.0.9
		*/
        function tpgb_category_thumbnail_save($term_id) {
            if (isset($_POST['tpgb_category_id'])) {
                update_term_meta($term_id, 'tpgb_category_id', $_POST['tpgb_category_id']);
            }
        }
        
        /**
		* Image Column in List
        * @since 2.0.9
		*/
        function tpgb_category_image_column( $columns ) {
    
            $new_columns = array();
    
            if ( isset( $columns['cb'] ) ) {
                $new_columns['cb'] = $columns['cb'];
                unset( $columns['cb'] );
            }
    
            $new_columns['category_image'] = __( 'Image', 'tpgbp' );
    
            $columns = array_merge( $new_columns, $columns );
            $columns['handle'] = '';
    
            return $columns;
        }
        
        /**
		* Image Column in List
        * @since 2.0.9
		*/
        function tpgb_category_image_column_value( $dep , $columns,$term_id ) {
            $html = '';
            if ( 'category_image' == $columns ) {
                $image_url = get_term_meta( $term_id, 'tpgb_category_id', true );
                if ( $image_url ) {
                    $html .= '<img src="'.esc_url( wp_get_attachment_url($image_url) ).'" alt="'.esc_attr__('Category Image','tpgbp').'" style="max-width: 100px; height: auto; display: block;">';
                }
            }

            return  $html;
        }       
    }
}

new Tpgbp_Add_Category_Img();