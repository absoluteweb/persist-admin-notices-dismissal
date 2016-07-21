<?php
/**
 * Persist Admin notices Dismissal
 *
 * Copyright (C) 2016  Agbonghama Collins <http://w3guy.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package Persist Admin notices Dismissal
 * @author Agbonghama Collins
 * @license http://www.gnu.org/licenses GNU General Public License
 */


add_action('admin_enqueue_scripts', function () {
    wp_enqueue_script('dismissible-notices',
        plugin_dir_url(__FILE__) . 'dismiss-notice.js',
        ['jquery', 'common'],
        false,
        true
    );

    wp_localize_script('dismissible-notices', 'dismissible_notice',
        array(
            'nonce' => wp_create_nonce('dismissible-notice'),
        )
    );
});


/**
 * Handles Ajax request to persist notices dismissal.
 */
add_action('wp_ajax_dismiss_admin_notice', function () {
    $option_name        = sanitize_text_field($_POST['option_name']);
    $dismissible_length = sanitize_text_field($_POST['dismissible_length']);

    if ($dismissible_length != 'forever') {
        $dismissible_length = time() + strtotime(absint($dismissible_length) . 'days');
    }

    if (wp_verify_nonce($_REQUEST['nonce'], 'pp-dismissible-notice') && strpos($option_name, 'data-') !== false) {
        add_option($option_name, $dismissible_length);
    }

    add_option($option_name, $dismissible_length);
    wp_die();
});


/**
 * Is admin notice active?
 *
 * @param string $arg
 *
 * @return bool
 */
function is_admin_notice_active($arg)
{
    $array       = explode('-', $arg);
    $length      = array_pop($array);
    $option_name = implode('-', $array);

    $db_record = get_option($option_name);

    if ($db_record == 'forever') {
        return false;
    } elseif (absint($db_record) >= time()) {
        return false;
    } else {
        return true;
    }
}