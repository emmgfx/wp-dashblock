<?php
/*
Plugin Name: WP Dashblock
Plugin URI:  http://www.emm-gfx.net
Description: Disable the Wordpress Dashboard, except for selected users.
Version:     0.1
Author:      Josep Viciana
Author URI:  http://www.emm-gfx.net
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: wp_dashblock

WP Dashblock is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
WP Dashblock is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with WP Dashblock. If not, see {License URI}.
*/

if ( ! defined( 'WPINC' ) ) {
	die;
}

function wp_dasbhblock_blocker(){
    
    if(!is_admin())
        return;
    
    $user = wp_get_current_user();
    
    if($user->allcaps['administrator'] === true)
        return;
    
    $users_with_access = explode(',', get_option('users_with_access'));
    
    if(in_array($user->user_login, $users_with_access))
        return;
        
    
    header('location: ' . get_home_url());
}

add_action('init', 'wp_dasbhblock_blocker');
add_action('admin_menu', 'wp_dashblock_options' );
 
function wp_dashblock_options() {
    add_options_page(
        'WP Dashblock',
        'WP Dashblock',
        'manage_options',
        'wp-dashblock',
        'wp_dashblock_options_page'
    );
}

function wp_dashblock_options_page() {
    ?>
    <div class="wrap">
        
        <h2>WP Dashblock options</h2>
    
        <h3>Don't disable the dashboard for this users:</h3>

        <h4>Administrators:</h4>
        
        <?php $administrators = get_users(array('role' => 'administrator')); ?>
        
        <style>
        .va-middle{
            vertical-align: middle;
        }
        </style>
        <ul>
            <?php foreach($administrators as $administrator): ?>
            <li>
                <?php echo get_avatar($administrator, 24, null, null, array('class' => 'va-middle')); ?>
                <?php echo $administrator->user_login; ?>
            </li>
            <?php endforeach; ?>
        </ul>
        
        <h4>Other users with dashboard enabled:</h4>
        

        <ul class="unlocked-users">
            <?php
            $users = explode(',', get_option('users_with_access'));

            foreach((array) $users as $user){
                if(trim($user) == '')
                    continue;
                echo '<li>' . trim($user) . ' <a href="#" class="revoke-access" data-user="' . trim($user) . '">Revoke access</a></li>';
            }
            ?>
        </ul>

        <h4>Add users to the whitelist:</h4>

        <select class="select-users">
            <option></option>
            <?php $users = get_users(); ?>
            <?php foreach($users as $user): ?>
            <option value="<?php echo $user->user_login; ?>"><?php echo $user->user_login; ?></option>
            <?php endforeach; ?>
        </select>
        <button class="button add-users">Add</button>
                
        <script>
        jQuery(document).ready(function() {

            var $ = jQuery;
            var select = $('.select-users');
            
            var users = readCSV();
            
            for(i=0; i<users.length; i++)
                select.find('option[value="' + users[i] + '"]').prop('disabled', true);
            
            $(document).on("click", ".add-users", function(){
                var selected = select.val();
                
                if(selected == null)
                    return;
                
                if(selected == '')
                    return;

                $(".unlocked-users").append('<li>' + selected + ' <a href="#" class="revoke-access" data-user="' + selected + '">Revoke access</a></li>')
                select.find('option[value="' + selected + '"]').prop('disabled', true);
                
                var users = readCSV();
                
                users.push(selected);
                
                saveCSV(users);
                    
                return false; 
            });

            $(document).on("click", ".revoke-access", function(){
                var user = $(this).data('user');
                select.find('option[value="' + user + '"]').prop('disabled', false);
                $(this).closest('li').remove();
                
                var users = readCSV();
                
                for(i = 0; i < users.length; i++){
                    if(users[i] == user){
                        users.splice(i, 1);
                        continue;
                    }
                }
                
                saveCSV(users);
                
                return false; 
            });
            
            function readCSV(){
                console.log('reading csv');
                var csvUsers = $('.csv-users');
                return csvUsers.val().split(',');
            }
            
            function saveCSV(users){
                console.log('saving csv');
                
                var csvUsers = $('.csv-users');
                
                var strCsvUsers = '';
                
                for(i = 0; i < users.length; i++)
                    if(users[i] != '')
                        strCsvUsers += ',' + users[i].trim();
                
                if(strCsvUsers.substr(0, 1) == ',')
                    strCsvUsers = strCsvUsers.substr(1)
                
                csvUsers.val(strCsvUsers);

            }
            
        });
        </script>
        
        <form method="post" action="options.php">
            <?PHP settings_fields( 'wp-dasblock-settings' ); ?>
            <?PHP do_settings_sections( 'wp-dasblock-settings' ); ?>
            <input type="hidden" class="csv-users" name="users_with_access" value="<?php echo get_option('users_with_access'); ?>" />
            <?php submit_button(); ?>
        </form>
        
        
    </div>
    <?php
}

add_action('admin_init', 'register_wp_dashblock_settings' );
function register_wp_dashblock_settings() {
    register_setting('wp-dasblock-settings', 'users_with_access');
}
?>