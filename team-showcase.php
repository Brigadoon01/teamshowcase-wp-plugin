<?php
/**
 * Plugin Name: Team Showcase
 * Description: A responsive team showcase component with filtering, search, and pagination - fetches data from custom post types and ACF fields
 * Version: 1.0.0
 * Author: Toye Jeremiah
 * Text Domain: team-showcase
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class TeamShowcase {
    
    /**
     * Plugin constructor
     */
    public function __construct() {
        // Define constants
        define('TEAM_SHOWCASE_PATH', plugin_dir_path(__FILE__));
        define('TEAM_SHOWCASE_URL', plugin_dir_url(__FILE__));
        define('TEAM_SHOWCASE_VERSION', '1.0.0');
        
        // Initialize plugin
        add_action('init', array($this, 'register_post_type'));
        add_action('init', array($this, 'register_block'));
        add_action('acf/init', array($this, 'register_acf_fields'));
        add_action('rest_api_init', array($this, 'register_rest_routes'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
        add_action('admin_notices', array($this, 'acf_admin_notice'));
        
        // Register shortcode
        add_shortcode('team_showcase', array($this, 'team_showcase_shortcode'));
        
        // Add admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
    }
    
    /**
     * Register Team Member custom post type
     */
    public function register_post_type() {
        $labels = array(
            'name'               => _x('Team Members', 'post type general name', 'team-showcase'),
            'singular_name'      => _x('Team Member', 'post type singular name', 'team-showcase'),
            'menu_name'          => _x('Team Members', 'admin menu', 'team-showcase'),
            'name_admin_bar'     => _x('Team Member', 'add new on admin bar', 'team-showcase'),
            'add_new'            => _x('Add New', 'team member', 'team-showcase'),
            'add_new_item'       => __('Add New Team Member', 'team-showcase'),
            'new_item'           => __('New Team Member', 'team-showcase'),
            'edit_item'          => __('Edit Team Member', 'team-showcase'),
            'view_item'          => __('View Team Member', 'team-showcase'),
            'all_items'          => __('All Team Members', 'team-showcase'),
            'search_items'       => __('Search Team Members', 'team-showcase'),
            'parent_item_colon'  => __('Parent Team Members:', 'team-showcase'),
            'not_found'          => __('No team members found.', 'team-showcase'),
            'not_found_in_trash' => __('No team members found in Trash.', 'team-showcase')
        );

        $args = array(
            'labels'             => $labels,
            'description'        => __('Team members for the Team Showcase.', 'team-showcase'),
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array('slug' => 'team-member'),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 20,
            'menu_icon'          => 'dashicons-groups',
            'supports'           => array('title', 'editor', 'thumbnail', 'excerpt'),
            'show_in_rest'       => true,
        );

        register_post_type('team_member', $args);
        
        // Register department taxonomy
        register_taxonomy(
            'team_department',
            'team_member',
            array(
                'label' => __('Departments', 'team-showcase'),
                'labels' => array(
                    'name' => __('Departments', 'team-showcase'),
                    'singular_name' => __('Department', 'team-showcase'),
                    'add_new_item' => __('Add New Department', 'team-showcase'),
                    'edit_item' => __('Edit Department', 'team-showcase'),
                ),
                'rewrite' => array('slug' => 'team-department'),
                'hierarchical' => true,
                'show_in_rest' => true,
                'show_admin_column' => true,
            )
        );
    }
    
    /**
     * Register ACF fields for team members
     */
    public function register_acf_fields() {
        if (!function_exists('acf_add_local_field_group')) {
            return;
        }
        
        acf_add_local_field_group(array(
            'key' => 'group_team_member_details',
            'title' => 'Team Member Details',
            'fields' => array(
                array(
                    'key' => 'field_job_title',
                    'label' => 'Job Title',
                    'name' => 'job_title',
                    'type' => 'text',
                    'required' => 1,
                    'instructions' => 'Enter the team member\'s job title or position.',
                ),
                array(
                    'key' => 'field_bio',
                    'label' => 'Bio',
                    'name' => 'bio',
                    'type' => 'textarea',
                    'rows' => 4,
                    'instructions' => 'Brief biography or description of the team member.',
                ),
                array(
                    'key' => 'field_linkedin_url',
                    'label' => 'LinkedIn URL',
                    'name' => 'linkedin_url',
                    'type' => 'url',
                    'instructions' => 'Full LinkedIn profile URL (e.g., https://linkedin.com/in/username)',
                ),
                array(
                    'key' => 'field_twitter_url',
                    'label' => 'Twitter URL',
                    'name' => 'twitter_url',
                    'type' => 'url',
                    'instructions' => 'Full Twitter profile URL (e.g., https://twitter.com/username)',
                ),
                array(
                    'key' => 'field_email',
                    'label' => 'Email',
                    'name' => 'email',
                    'type' => 'email',
                    'instructions' => 'Team member\'s email address (optional).',
                ),
                array(
                    'key' => 'field_phone',
                    'label' => 'Phone',
                    'name' => 'phone',
                    'type' => 'text',
                    'instructions' => 'Team member\'s phone number (optional).',
                ),
                array(
                    'key' => 'field_display_order',
                    'label' => 'Display Order',
                    'name' => 'display_order',
                    'type' => 'number',
                    'default_value' => 0,
                    'instructions' => 'Order in which this team member should appear (lower numbers appear first).',
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'team_member',
                    ),
                ),
            ),
            'menu_order' => 0,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
        ));
    }
    
    /**
     * Register REST API routes
     */
    public function register_rest_routes() {
        register_rest_route('team-showcase/v1', '/members', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_team_members'),
            'permission_callback' => '__return_true',
            'args' => array(
                'department' => array(
                    'description' => 'Filter by department slug',
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'search' => array(
                    'description' => 'Search term for name or job title',
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'per_page' => array(
                    'description' => 'Number of items per page',
                    'type' => 'integer',
                    'default' => -1,
                    'minimum' => -1,
                ),
                'page' => array(
                    'description' => 'Page number',
                    'type' => 'integer',
                    'default' => 1,
                    'minimum' => 1,
                ),
            ),
        ));
        
        register_rest_route('team-showcase/v1', '/departments', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_departments'),
            'permission_callback' => '__return_true',
        ));
    }
    
    /**
     * Get team members for REST API
     */
    public function get_team_members($request) {
        $department = $request->get_param('department');
        $search = $request->get_param('search');
        $per_page = $request->get_param('per_page');
        $page = $request->get_param('page');
        
        $args = array(
            'post_type' => 'team_member',
            'post_status' => 'publish',
            'posts_per_page' => $per_page,
            'paged' => $page,
            'meta_key' => 'display_order',
            'orderby' => array(
                'meta_value_num' => 'ASC',
                'title' => 'ASC'
            ),
        );
        
        // Filter by department
        if ($department && $department !== 'all') {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'team_department',
                    'field' => 'slug',
                    'terms' => $department,
                ),
            );
        }
        
        // Search functionality
        if ($search) {
            $args['meta_query'] = array(
                'relation' => 'OR',
                array(
                    'key' => 'job_title',
                    'value' => $search,
                    'compare' => 'LIKE'
                )
            );
            
            // Also search in post title
            add_filter('posts_where', function($where) use ($search) {
                global $wpdb;
                $where .= $wpdb->prepare(" OR {$wpdb->posts}.post_title LIKE %s", '%' . $wpdb->esc_like($search) . '%');
                return $where;
            });
        }
        
        $query = new WP_Query($args);
        $members = array();
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                
                // Get department terms
                $department_terms = get_the_terms($post_id, 'team_department');
                $department_name = '';
                $department_slug = '';
                
                if (!empty($department_terms) && !is_wp_error($department_terms)) {
                    $department_name = $department_terms[0]->name;
                    $department_slug = $department_terms[0]->slug;
                }
                
                // Get featured image
                $photo_url = get_the_post_thumbnail_url($post_id, 'medium');
                if (!$photo_url) {
                    $photo_url = TEAM_SHOWCASE_URL . 'assets/images/placeholder.png';
                }
                
                // Get ACF fields
                $job_title = get_field('job_title', $post_id);
                $bio = get_field('bio', $post_id);
                $linkedin_url = get_field('linkedin_url', $post_id);
                $twitter_url = get_field('twitter_url', $post_id);
                $email = get_field('email', $post_id);
                $phone = get_field('phone', $post_id);
                $display_order = get_field('display_order', $post_id);
                
                $members[] = array(
                    'id' => $post_id,
                    'name' => get_the_title(),
                    'jobTitle' => $job_title ?: '',
                    'bio' => $bio ?: '',
                    'department' => $department_name,
                    'departmentSlug' => $department_slug,
                    'photo' => $photo_url,
                    'excerpt' => get_the_excerpt(),
                    'socialLinks' => array(
                        'linkedin' => $linkedin_url ?: '',
                        'twitter' => $twitter_url ?: '',
                    ),
                    'contact' => array(
                        'email' => $email ?: '',
                        'phone' => $phone ?: '',
                    ),
                    'displayOrder' => intval($display_order),
                    'permalink' => get_permalink(),
                );
            }
            wp_reset_postdata();
        }
        
        // Remove the search filter
        if ($search) {
            remove_all_filters('posts_where');
        }
        
        $response = array(
            'members' => $members,
            'total' => $query->found_posts,
            'pages' => $query->max_num_pages,
        );
        
        return rest_ensure_response($response);
    }
    
    /**
     * Get departments for REST API
     */
    public function get_departments($request) {
        $terms = get_terms(array(
            'taxonomy' => 'team_department',
            'hide_empty' => true,
        ));
        
        $departments = array();
        
        if (!is_wp_error($terms)) {
            foreach ($terms as $term) {
                $departments[] = array(
                    'id' => $term->term_id,
                    'name' => $term->name,
                    'slug' => $term->slug,
                    'count' => $term->count,
                );
            }
        }
        
        return rest_ensure_response($departments);
    }
    
    /**
     * Register Gutenberg block
     */
    public function register_block() {
        // Skip if Gutenberg is not available
        if (!function_exists('register_block_type')) {
            return;
        }
        
        // Register block script
        wp_register_script(
            'team-showcase-block-editor',
            TEAM_SHOWCASE_URL . 'assets/js/block.js',
            array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components'),
            TEAM_SHOWCASE_VERSION
        );
        
        // Register block
        register_block_type('team-showcase/team-grid', array(
            'editor_script' => 'team-showcase-block-editor',
            'attributes' => array(
                'itemsPerPage' => array(
                    'type' => 'number',
                    'default' => 6,
                ),
                'showSearch' => array(
                    'type' => 'boolean',
                    'default' => true,
                ),
                'showDepartmentFilter' => array(
                    'type' => 'boolean',
                    'default' => true,
                ),
                'department' => array(
                    'type' => 'string',
                    'default' => 'all',
                ),
            ),
            'render_callback' => array($this, 'render_block'),
        ));
    }
    
    /**
     * Render Gutenberg block
     */
    public function render_block($attributes) {
        $items_per_page = isset($attributes['itemsPerPage']) ? intval($attributes['itemsPerPage']) : 6;
        $show_search = isset($attributes['showSearch']) ? $attributes['showSearch'] : true;
        $show_department_filter = isset($attributes['showDepartmentFilter']) ? $attributes['showDepartmentFilter'] : true;
        $department = isset($attributes['department']) ? $attributes['department'] : 'all';
        
        return sprintf(
            '<div class="team-showcase-container" data-items-per-page="%d" data-show-search="%s" data-show-department-filter="%s" data-department="%s"></div>',
            $items_per_page,
            $show_search ? 'true' : 'false',
            $show_department_filter ? 'true' : 'false',
            esc_attr($department)
        );
    }
    
    /**
     * Shortcode handler
     */
    public function team_showcase_shortcode($atts) {
        $atts = shortcode_atts(array(
            'items_per_page' => 6,
            'show_search' => 'true',
            'show_department_filter' => 'true',
            'department' => 'all',
        ), $atts);
        
        return sprintf(
            '<div class="team-showcase-container" data-items-per-page="%d" data-show-search="%s" data-show-department-filter="%s" data-department="%s"></div>',
            intval($atts['items_per_page']),
            $atts['show_search'],
            $atts['show_department_filter'],
            esc_attr($atts['department'])
        );
    }
    
    /**
     * Enqueue frontend scripts
     */
    public function enqueue_scripts() {
        wp_enqueue_style(
            'team-showcase-style',
            TEAM_SHOWCASE_URL . 'assets/css/team-showcase.css',
            array(),
            TEAM_SHOWCASE_VERSION
        );
        
        wp_enqueue_script(
            'team-showcase-script',
            TEAM_SHOWCASE_URL . 'assets/js/team-showcase.js',
            array('jquery'),
            TEAM_SHOWCASE_VERSION,
            true
        );
        
        wp_localize_script('team-showcase-script', 'teamShowcaseData', array(
            'apiUrl' => rest_url('team-showcase/v1/members'),
            'departmentsUrl' => rest_url('team-showcase/v1/departments'),
            'nonce' => wp_create_nonce('wp_rest'),
            'pluginUrl' => TEAM_SHOWCASE_URL,
        ));
    }
    
    /**
     * Enqueue admin scripts
     */
    public function admin_scripts($hook) {
        if ('post.php' !== $hook && 'post-new.php' !== $hook) {
            return;
        }
        
        global $post;
        
        if ('team_member' !== $post->post_type) {
            return;
        }
        
        wp_enqueue_style(
            'team-showcase-admin-style',
            TEAM_SHOWCASE_URL . 'assets/css/admin.css',
            array(),
            TEAM_SHOWCASE_VERSION
        );
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'edit.php?post_type=team_member',
            'Team Showcase Settings',
            'Settings',
            'manage_options',
            'team-showcase-settings',
            array($this, 'admin_page')
        );
    }
    
    /**
     * Admin settings page
     */
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1>Team Showcase Settings</h1>
            
            <div class="card">
                <h2>Usage Instructions</h2>
                <p>Use the Team Showcase in your posts and pages:</p>
                
                <h3>Gutenberg Block</h3>
                <p>Search for "Team Showcase" in the block editor and add it to your page.</p>
                
                <h3>Shortcode</h3>
                <p>Use the following shortcode:</p>
                <code>[team_showcase items_per_page="6" show_search="true" show_department_filter="true" department="all"]</code>
                
                <h4>Shortcode Parameters:</h4>
                <ul>
                    <li><strong>items_per_page</strong>: Number of team members per page (default: 6)</li>
                    <li><strong>show_search</strong>: Show search input (true/false, default: true)</li>
                    <li><strong>show_department_filter</strong>: Show department filter (true/false, default: true)</li>
                    <li><strong>department</strong>: Show only specific department (use department slug, default: all)</li>
                </ul>
            </div>
            
            <div class="card">
                <h2>REST API Endpoints</h2>
                <p>The plugin provides REST API endpoints for developers:</p>
                <ul>
                    <li><strong>GET</strong> <code><?php echo rest_url('team-showcase/v1/members'); ?></code> - Get team members</li>
                    <li><strong>GET</strong> <code><?php echo rest_url('team-showcase/v1/departments'); ?></code> - Get departments</li>
                </ul>
            </div>
        </div>
        <?php
    }
    
    /**
     * Show admin notice if ACF is not installed
     */
    public function acf_admin_notice() {
        if (!function_exists('acf_add_local_field_group')) {
            ?>
            <div class="notice notice-warning">
                <p><strong>Team Showcase:</strong> This plugin works best with Advanced Custom Fields (ACF) plugin installed. <a href="<?php echo admin_url('plugin-install.php?s=advanced+custom+fields&tab=search&type=term'); ?>">Install ACF</a></p>
            </div>
            <?php
        }
    }
}

// Initialize the plugin
new TeamShowcase();

// Activation hook
register_activation_hook(__FILE__, function() {
    // Flush rewrite rules
    flush_rewrite_rules();
});

// Deactivation hook
register_deactivation_hook(__FILE__, function() {
    // Flush rewrite rules
    flush_rewrite_rules();
});
